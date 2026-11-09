package org.straguel.attendance

import android.Manifest
import android.content.pm.PackageManager
import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.Spinner
import android.widget.TextView
import androidx.activity.ComponentActivity
import androidx.activity.result.contract.ActivityResultContracts
import androidx.camera.core.CameraSelector
import androidx.camera.core.ImageAnalysis
import androidx.camera.core.Preview
import androidx.camera.lifecycle.ProcessCameraProvider
import androidx.camera.view.PreviewView
import androidx.core.content.ContextCompat
import androidx.lifecycle.lifecycleScope
import com.google.mlkit.vision.barcode.BarcodeScanner
import com.google.mlkit.vision.barcode.BarcodeScannerOptions
import com.google.mlkit.vision.barcode.BarcodeScanning
import com.google.mlkit.vision.barcode.common.Barcode
import com.google.mlkit.vision.common.InputImage
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.HttpUrl.Companion.toHttpUrlOrNull
import org.json.JSONObject
import java.io.IOException
import java.util.concurrent.Executors
import java.util.concurrent.atomic.AtomicBoolean
import java.util.concurrent.TimeUnit

private data class AttendanceSession(
    val id: Int,
    val title: String,
    val start: String,
    val opensAt: String,
    val closesAt: String,
    val location: String,
    val checkinOpen: Boolean
) {
    override fun toString(): String =
        "$title · $start · ${if (checkinOpen) "Check-in open" else "Outside check-in window"}"
}

private class ApiException(message: String) : IOException(message)

class MainActivity : ComponentActivity() {
    private val secureStore by lazy { SecureSessionStore(this) }
    private val httpClient = OkHttpClient.Builder()
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(20, TimeUnit.SECONDS)
        .callTimeout(30, TimeUnit.SECONDS)
        .followRedirects(false)
        .build()
    private val cameraExecutor = Executors.newSingleThreadExecutor()
    private val scanInProgress = AtomicBoolean(false)
    private val barcodeScanner: BarcodeScanner by lazy {
        BarcodeScanning.getClient(
            BarcodeScannerOptions.Builder()
                .setBarcodeFormats(Barcode.FORMAT_QR_CODE)
                .build()
        )
    }

    private lateinit var statusText: TextView
    private lateinit var loginPanel: LinearLayout
    private lateinit var attendancePanel: LinearLayout
    private lateinit var serverUrlInput: EditText
    private lateinit var emailInput: EditText
    private lateinit var passwordInput: EditText
    private lateinit var sessionSpinner: Spinner
    private lateinit var cameraPreview: PreviewView
    private lateinit var loginButton: Button

    private var serverUrl: String? = null
    private var accessToken: String? = null
    private var sessions: List<AttendanceSession> = emptyList()
    private var cameraProvider: ProcessCameraProvider? = null

    private val cameraPermission = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { granted ->
        if (granted) startCamera() else showStatus("Allow camera access to scan member QR codes.")
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        statusText = findViewById(R.id.statusText)
        loginPanel = findViewById(R.id.loginPanel)
        attendancePanel = findViewById(R.id.attendancePanel)
        serverUrlInput = findViewById(R.id.serverUrlInput)
        emailInput = findViewById(R.id.emailInput)
        passwordInput = findViewById(R.id.passwordInput)
        sessionSpinner = findViewById(R.id.sessionSpinner)
        cameraPreview = findViewById(R.id.cameraPreview)
        loginButton = findViewById(R.id.loginButton)

        findViewById<Button>(R.id.loginButton).setOnClickListener { signIn() }
        findViewById<Button>(R.id.refreshButton).setOnClickListener { loadSessions() }
        findViewById<Button>(R.id.logoutButton).setOnClickListener { signOut() }
        sessionSpinner.onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
            override fun onItemSelected(
                parent: android.widget.AdapterView<*>?,
                view: View?,
                position: Int,
                id: Long
            ) {
                updateSelectedSession()
            }

            override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {
                stopCamera()
            }
        }

        try {
            val saved = secureStore.read()
            if (saved != null) {
                serverUrl = normalizeServerUrl(saved.serverUrl)
                accessToken = saved.token
                serverUrlInput.setText(saved.serverUrl)
                showAttendanceUi()
                loadSessions()
            }
        } catch (exception: Exception) {
            secureStore.clear()
            showStatus(exception.message ?: "Saved sign-in could not be opened. Please sign in again.")
        }
    }

    private fun signIn() {
        val normalizedServer = try {
            normalizeServerUrl(serverUrlInput.text.toString())
        } catch (exception: IllegalArgumentException) {
            showStatus(exception.message ?: "Enter a valid HTTPS website URL.")
            return
        }
        val email = emailInput.text.toString().trim()
        val password = passwordInput.text.toString()
        if (email.isEmpty() || password.isEmpty()) {
            showStatus("Enter your Attendance Holder email and password.")
            return
        }

        loginButton.isEnabled = false
        showStatus("Signing in…")
        lifecycleScope.launch {
            try {
                val response = apiRequest(
                    normalizedServer,
                    "login.php",
                    null,
                    JSONObject().put("email", email).put("password", password)
                )
                val token = response.optString("token")
                if (!token.matches(Regex("^[a-f0-9]{64}$"))) {
                    throw ApiException("The server returned an invalid sign-in response.")
                }
                secureStore.save(normalizedServer, token)
                serverUrl = normalizedServer
                accessToken = token
                passwordInput.text.clear()
                showAttendanceUi()
                showStatus("Signed in. Loading your assigned sessions…")
                loadSessions()
            } catch (exception: Exception) {
                showStatus(exception.message ?: "Could not sign in. Check the URL and connection.")
            } finally {
                loginButton.isEnabled = true
            }
        }
    }

    private fun loadSessions() {
        val base = serverUrl ?: return
        if (accessToken == null) return
        showStatus("Loading assigned sessions…")
        lifecycleScope.launch {
            try {
                val response = apiRequest(base, "sessions.php", accessToken)
                val rows = response.optJSONArray("sessions")
                    ?: throw ApiException("The server returned an invalid session list.")
                sessions = (0 until rows.length()).mapNotNull { index ->
                    val row = rows.optJSONObject(index) ?: return@mapNotNull null
                    AttendanceSession(
                        id = row.optInt("id"),
                        title = row.optString("title"),
                        start = row.optString("session_start"),
                        opensAt = row.optString("checkin_opens_at"),
                        closesAt = row.optString("checkin_closes_at"),
                        location = row.optString("location"),
                        checkinOpen = row.optBoolean("checkin_open")
                    )
                }

                val adapter = ArrayAdapter(
                    this@MainActivity,
                    android.R.layout.simple_spinner_item,
                    sessions
                )
                adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
                sessionSpinner.adapter = adapter
                if (sessions.isEmpty()) {
                    stopCamera()
                    cameraPreview.visibility = View.GONE
                    showStatus("No open sessions are assigned to your account. Ask a Controller to assign one.")
                } else {
                    cameraPreview.visibility = View.VISIBLE
                    updateSelectedSession()
                }
            } catch (exception: Exception) {
                if (exception is ApiException && exception.message?.contains("Sign in again") == true) {
                    secureStore.clear()
                    accessToken = null
                    serverUrl = null
                    showLoginUi()
                }
                showStatus(exception.message ?: "Could not load sessions. Check the connection.")
            }
        }
    }

    private fun updateSelectedSession() {
        val selected = sessions.getOrNull(sessionSpinner.selectedItemPosition)
        if (selected == null) {
            stopCamera()
            return
        }
        if (selected.checkinOpen) {
            showStatus("Check-in is open for ${selected.title}. Point the camera at a member QR code.")
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED) {
                startCamera()
            } else {
                cameraPermission.launch(Manifest.permission.CAMERA)
            }
        } else {
            stopCamera()
            showStatus("Check-in is available from ${selected.opensAt} until ${selected.closesAt}.")
        }
    }

    private fun startCamera() {
        val selected = sessions.getOrNull(sessionSpinner.selectedItemPosition)
        if (selected == null || !selected.checkinOpen) return
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
            cameraPermission.launch(Manifest.permission.CAMERA)
            return
        }

        val providerFuture = ProcessCameraProvider.getInstance(this)
        providerFuture.addListener({
            try {
                val provider = providerFuture.get()
                cameraProvider = provider
                val preview = Preview.Builder().build().also {
                    it.surfaceProvider = cameraPreview.surfaceProvider
                }
                val analysis = ImageAnalysis.Builder()
                    .setBackpressureStrategy(ImageAnalysis.STRATEGY_KEEP_ONLY_LATEST)
                    .build()
                analysis.setAnalyzer(cameraExecutor) { imageProxy ->
                    val mediaImage = imageProxy.image
                    if (mediaImage == null) {
                        imageProxy.close()
                    } else {
                        val image = InputImage.fromMediaImage(mediaImage, imageProxy.imageInfo.rotationDegrees)
                        barcodeScanner.process(image)
                            .addOnSuccessListener { barcodes ->
                                val value = barcodes.firstOrNull()?.rawValue
                                if (value != null) handleScannedCode(value)
                            }
                            .addOnFailureListener {
                                runOnUiThread { showStatus("Could not read the QR image. Hold the camera steady and try again.") }
                            }
                            .addOnCompleteListener { imageProxy.close() }
                    }
                }

                provider.unbindAll()
                provider.bindToLifecycle(
                    this,
                    CameraSelector.DEFAULT_BACK_CAMERA,
                    preview,
                    analysis
                )
            } catch (exception: Exception) {
                showStatus(exception.message ?: "Could not start the camera.")
            }
        }, ContextCompat.getMainExecutor(this))
    }

    private fun handleScannedCode(value: String) {
        if (!value.matches(Regex("^[a-fA-F0-9]{32}$")) || !scanInProgress.compareAndSet(false, true)) return
        val selected = sessions.getOrNull(sessionSpinner.selectedItemPosition) ?: run {
            scanInProgress.set(false)
            return
        }
        if (!selected.checkinOpen) {
            scanInProgress.set(false)
            runOnUiThread { showStatus("This session is outside its check-in window.") }
            return
        }

        runOnUiThread { showStatus("Checking member QR code…") }
        lifecycleScope.launch {
            try {
                val base = serverUrl ?: throw ApiException("Sign in again to continue.")
                val token = accessToken ?: throw ApiException("Sign in again to continue.")
                val response = apiRequest(
                    base,
                    "scan.php",
                    token,
                    JSONObject().put("session_id", selected.id).put("qr_token", value)
                )
                val member = response.optJSONObject("member")
                val name = member?.optString("name").orEmpty()
                if (name.isBlank()) throw ApiException("The server returned an invalid check-in response.")
                showStatus("Checked in: $name")
            } catch (exception: Exception) {
                showStatus(exception.message ?: "Could not save this check-in.")
            } finally {
                delay(1200)
                scanInProgress.set(false)
            }
        }
    }

    private fun signOut() {
        val base = serverUrl
        val token = accessToken
        stopCamera()
        lifecycleScope.launch {
            var message = "Signed out."
            try {
                if (base != null && token != null) apiRequest(base, "logout.php", token, JSONObject())
            } catch (exception: Exception) {
                message = "Signed out on this device. The server token could not be revoked; it expires automatically."
            } finally {
                secureStore.clear()
                serverUrl = null
                accessToken = null
                sessions = emptyList()
                showLoginUi()
                showStatus(message)
            }
        }
    }

    private suspend fun apiRequest(
        base: String,
        endpoint: String,
        token: String? = null,
        body: JSONObject? = null
    ): JSONObject = withContext(Dispatchers.IO) {
        val mediaType = "application/json; charset=utf-8".toMediaType()
        val requestBody = body?.toString()?.toRequestBody(mediaType)
        val builder = Request.Builder()
            .url("${base}Members/api/$endpoint")
            .header("Accept", "application/json")
        if (token != null) builder.header("Authorization", "Bearer $token")
        if (requestBody != null) builder.post(requestBody) else builder.get()

        httpClient.newCall(builder.build()).execute().use { response ->
            val responseText = response.body?.string().orEmpty()
            val payload = try {
                JSONObject(responseText)
            } catch (exception: org.json.JSONException) {
                throw ApiException("The server returned an unreadable response.")
            }
            if (!response.isSuccessful) {
                throw ApiException(payload.optString("error").ifBlank { "Server request failed (${response.code})." })
            }
            payload
        }
    }

    private fun normalizeServerUrl(rawUrl: String): String {
        val normalized = rawUrl.trim().trimEnd('/') + "/"
        val parsed = normalized.toHttpUrlOrNull()
        require(
            parsed != null && parsed.isHttps && parsed.query == null
                && parsed.fragment == null && parsed.username.isEmpty() && parsed.password.isEmpty()
        ) {
            "Enter the website's HTTPS base URL, including its project folder if applicable."
        }
        return normalized
    }

    private fun showAttendanceUi() {
        loginPanel.visibility = View.GONE
        attendancePanel.visibility = View.VISIBLE
    }

    private fun showLoginUi() {
        stopCamera()
        attendancePanel.visibility = View.GONE
        loginPanel.visibility = View.VISIBLE
    }

    private fun showStatus(message: String) {
        statusText.text = message
    }

    private fun stopCamera() {
        cameraProvider?.unbindAll()
    }

    override fun onDestroy() {
        stopCamera()
        barcodeScanner.close()
        cameraExecutor.shutdown()
        httpClient.dispatcher.cancelAll()
        super.onDestroy()
    }
}
