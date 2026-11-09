package org.straguel.attendance

import android.content.Context
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import java.nio.charset.StandardCharsets
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

data class SavedSession(val serverUrl: String, val token: String)

class SecureSessionStore(context: Context) {
    private val preferences = context.getSharedPreferences("attendance_session", Context.MODE_PRIVATE)
    private val keyAlias = "st_raguel_attendance_token"

    fun save(serverUrl: String, token: String) {
        val cipher = Cipher.getInstance("AES/GCM/NoPadding")
        cipher.init(Cipher.ENCRYPT_MODE, getOrCreateKey())
        val encrypted = cipher.doFinal(token.toByteArray(StandardCharsets.UTF_8))
        val stored = cipher.iv + encrypted
        preferences.edit()
            .putString("server_url", serverUrl)
            .putString("token", Base64.encodeToString(stored, Base64.NO_WRAP))
            .apply()
    }

    fun read(): SavedSession? {
        val encoded = preferences.getString("token", null) ?: return null
        val serverUrl = preferences.getString("server_url", null)
            ?: throw IllegalStateException("Saved sign-in has no server URL. Sign in again.")
        val stored = Base64.decode(encoded, Base64.NO_WRAP)
        if (stored.size <= 12) {
            throw IllegalStateException("Saved sign-in data is invalid. Sign in again.")
        }

        val cipher = Cipher.getInstance("AES/GCM/NoPadding")
        cipher.init(Cipher.DECRYPT_MODE, getOrCreateKey(), GCMParameterSpec(128, stored.copyOfRange(0, 12)))
        val token = cipher.doFinal(stored.copyOfRange(12, stored.size)).toString(StandardCharsets.UTF_8)
        return SavedSession(serverUrl, token)
    }

    fun clear() {
        preferences.edit().clear().apply()
    }

    private fun getOrCreateKey(): SecretKey {
        val keyStore = KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
        val existing = keyStore.getKey(keyAlias, null) as? SecretKey
        if (existing != null) return existing

        val generator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore")
        generator.init(
            KeyGenParameterSpec.Builder(
                keyAlias,
                KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
            )
                .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
                .setRandomizedEncryptionRequired(true)
                .build()
        )
        return generator.generateKey()
    }
}
