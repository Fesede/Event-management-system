(() => {
    'use strict';

    const elements = {
        appStatus: document.getElementById('app-status'),
        absentCount: document.getElementById('absent-count'),
        attendanceCounts: document.getElementById('attendance-counts'),
        attendancePanel: document.getElementById('attendance-panel'),
        excusedCount: document.getElementById('excused-count'),
        email: document.getElementById('email'),
        installButton: document.getElementById('install-button'),
        loginButton: document.getElementById('login-button'),
        loginForm: document.getElementById('login-form'),
        loginPanel: document.getElementById('login-panel'),
        memberList: document.getElementById('member-list'),
        password: document.getElementById('password'),
        presentCount: document.getElementById('present-count'),
        qrReader: document.getElementById('qr-reader'),
        refreshButton: document.getElementById('refresh-button'),
        rosterCount: document.getElementById('roster-count'),
        rosterPanel: document.getElementById('roster-panel'),
        rosterSearch: document.getElementById('roster-search'),
        scannerPanel: document.getElementById('scanner-panel'),
        sessionDetails: document.getElementById('session-details'),
        sessionSelect: document.getElementById('session-select'),
        sessionSummary: document.getElementById('session-summary'),
        sessionTitle: document.getElementById('session-title'),
        sessionWindow: document.getElementById('session-window'),
        signedInName: document.getElementById('signed-in-name'),
        signOutButton: document.getElementById('sign-out-button'),
        startScanButton: document.getElementById('start-scan-button'),
        stopScanButton: document.getElementById('stop-scan-button')
    };

    let accessToken = sessionStorage.getItem('attendance-token');
    let sessions = [];
    let scanner = null;
    let scannerRunning = false;
    let scanPending = false;
    let rosterRequest = 0;
    let installPrompt = null;

    function showStatus(message, kind = 'info') {
        elements.appStatus.textContent = message;
        elements.appStatus.dataset.kind = kind;
    }

    function showAttendance(name) {
        elements.loginPanel.hidden = true;
        elements.attendancePanel.hidden = false;
        elements.signedInName.textContent = name || 'Attendance Holder';
    }

    function showLogin() {
        elements.attendancePanel.hidden = true;
        elements.loginPanel.hidden = false;
        elements.loginForm.reset();
    }

    async function apiRequest(endpoint, {method = 'GET', body} = {}) {
        const headers = {Accept: 'application/json'};
        if (body !== undefined) headers['Content-Type'] = 'application/json';
        if (accessToken) headers.Authorization = `Bearer ${accessToken}`;

        const response = await fetch(`api/${endpoint}`, {
            method,
            headers,
            body: body === undefined ? undefined : JSON.stringify(body),
            cache: 'no-store',
            credentials: 'omit',
            redirect: 'error'
        });

        let payload;
        try {
            payload = await response.json();
        } catch {
            throw new Error('The server returned an unreadable response.');
        }

        if (!response.ok) {
            const error = new Error(payload.error || `Server request failed (${response.status}).`);
            error.status = response.status;
            throw error;
        }
        return payload;
    }

    function formatDate(value) {
        if (!value) return 'Time not provided';
        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return value;
        return new Intl.DateTimeFormat(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short'
        }).format(date);
    }

    function selectedSession() {
        const id = Number(elements.sessionSelect.value);
        return sessions.find(session => session.id === id) || null;
    }

    async function stopCamera() {
        if (scanner && scannerRunning) {
            await scanner.stop();
            scannerRunning = false;
        }
        if (scanner) {
            await scanner.clear();
            scanner = null;
        }
        elements.qrReader.hidden = true;
        elements.stopScanButton.hidden = true;
        elements.startScanButton.hidden = !selectedSession()?.checkin_open;
    }

    function updateSelectedSession() {
        const session = selectedSession();
        void stopCamera().catch(() => {
            showStatus('Could not stop the camera. Close this page and try again.', 'error');
        });
        elements.sessionSummary.hidden = !session;
        elements.scannerPanel.hidden = !session;
        elements.rosterPanel.hidden = !session;
        if (!session) return;

        elements.sessionTitle.textContent = session.title;
        const details = [formatDate(session.session_start), session.location].filter(Boolean);
        elements.sessionDetails.textContent = details.join(' · ');
        elements.sessionWindow.textContent = session.checkin_open
            ? 'Check-in is open now.'
            : `Check-in is available from ${formatDate(session.checkin_opens_at)} until ${formatDate(session.checkin_closes_at)}.`;
        updateAttendanceCounts(session);
        elements.startScanButton.hidden = !session.checkin_open;
        void loadRoster(session.id);
        showStatus(
            session.checkin_open
                ? 'Check-in is open. Start the camera to scan a member QR code.'
                : 'This session is outside its check-in window.'
        );
    }

    function updateAttendanceCounts(counts) {
        elements.presentCount.textContent = String(counts.present_count ?? 0);
        elements.excusedCount.textContent = String(counts.excused_count ?? 0);
        elements.rosterCount.textContent = String(counts.roster_count ?? 0);
        elements.absentCount.textContent = String(
            counts.absent_count ?? Math.max(0, (counts.roster_count ?? 0) - (counts.present_count ?? 0) - (counts.excused_count ?? 0))
        );
        elements.attendanceCounts.hidden = false;
    }

    function renderRoster(members) {
        elements.memberList.replaceChildren();
        const query = elements.rosterSearch.value.trim().toLocaleLowerCase();
        if (members.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'member-card-meta';
            empty.textContent = 'This session roster is empty.';
            elements.memberList.append(empty);
            return;
        }

        members.forEach(member => {
            const card = document.createElement('article');
            card.className = 'member-card';
            card.dataset.memberName = member.name.toLocaleLowerCase();
            card.hidden = !card.dataset.memberName.includes(query);

            const heading = document.createElement('div');
            heading.className = 'member-card-heading';
            const name = document.createElement('h4');
            name.textContent = member.name;
            const button = document.createElement('button');
            button.className = 'button button-quiet';
            button.type = 'button';
            button.textContent = 'Show QR';
            button.setAttribute('aria-expanded', 'false');
            heading.append(name, button);

            const meta = document.createElement('p');
            meta.className = 'member-card-meta';
            const attendance = member.attendance_status === 'absent'
                ? 'Not checked in'
                : `${member.attendance_status[0].toUpperCase()}${member.attendance_status.slice(1)}${member.recorded_at ? ` · ${formatDate(member.recorded_at)}` : ''}`;
            meta.textContent = `#${member.id} · ${attendance}`;

            const qrPanel = document.createElement('div');
            qrPanel.className = 'member-qr';
            qrPanel.hidden = true;
            button.addEventListener('click', () => {
                if (!qrPanel.hidden) {
                    qrPanel.hidden = true;
                    qrPanel.replaceChildren();
                    button.textContent = 'Show QR';
                    button.setAttribute('aria-expanded', 'false');
                    return;
                }

                if (typeof window.QRCode !== 'function') {
                    showStatus('The QR display could not load. Check your connection and reload the app.', 'error');
                    return;
                }

                qrPanel.replaceChildren();
                new window.QRCode(qrPanel, {
                    text: member.qr_token,
                    width: 176,
                    height: 176,
                    correctLevel: window.QRCode.CorrectLevel.M
                });
                const hint = document.createElement('p');
                hint.textContent = 'Member attendance code · show this code to the Attendance Holder.';
                qrPanel.append(hint);
                qrPanel.hidden = false;
                button.textContent = 'Hide QR';
                button.setAttribute('aria-expanded', 'true');
            });

            card.append(heading, meta, qrPanel);
            elements.memberList.append(card);
        });
    }

    async function loadRoster(sessionId) {
        const requestId = ++rosterRequest;
        elements.memberList.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'member-card-meta';
        loading.textContent = 'Loading session roster…';
        elements.memberList.append(loading);

        try {
            const result = await apiRequest(`roster.php?session_id=${encodeURIComponent(sessionId)}`);
            if (requestId !== rosterRequest || selectedSession()?.id !== sessionId) return;
            if (!Array.isArray(result.members) || !result.counts) {
                throw new Error('The server returned an invalid session roster.');
            }
            const members = result.members.filter(member =>
                Number.isInteger(member.id)
                && typeof member.name === 'string'
                && /^[a-f0-9]{32}$/.test(member.qr_token)
                && ['present', 'excused', 'absent'].includes(member.attendance_status)
            );
            updateAttendanceCounts(result.counts);
            renderRoster(members);
        } catch (error) {
            if (requestId !== rosterRequest || selectedSession()?.id !== sessionId) return;
            elements.memberList.replaceChildren();
            const message = document.createElement('p');
            message.className = 'member-card-meta';
            message.textContent = error.message || 'Could not load the session roster.';
            elements.memberList.append(message);
            if (error.status === 401) {
                accessToken = null;
                sessionStorage.removeItem('attendance-token');
                sessionStorage.removeItem('attendance-name');
                try {
                    await stopCamera();
                } catch (stopError) {
                    console.error('Could not stop the camera after the sign-in expired.', stopError);
                }
                sessions = [];
                rosterRequest++;
                showLogin();
                showStatus(error.message, 'error');
            }
        }
    }

    async function loadSessions() {
        elements.refreshButton.disabled = true;
        elements.sessionSelect.disabled = true;
        showStatus('Loading your assigned sessions…');
        try {
            const previousId = elements.sessionSelect.value;
            const result = await apiRequest('sessions.php');
            if (!Array.isArray(result.sessions)) {
                throw new Error('The server returned an invalid session list.');
            }
            sessions = result.sessions.filter(session =>
                Number.isInteger(session.id) && typeof session.title === 'string'
            );
            elements.sessionSelect.replaceChildren();

            if (sessions.length === 0) {
                elements.sessionSelect.add(new Option('No assigned sessions', ''));
                elements.sessionSelect.disabled = true;
                await stopCamera();
                elements.sessionSummary.hidden = true;
                elements.scannerPanel.hidden = true;
                elements.rosterPanel.hidden = true;
                elements.attendanceCounts.hidden = true;
                elements.memberList.replaceChildren();
                showStatus('No open sessions are assigned to your account. Ask a Controller to assign one.');
                return;
            }

            elements.sessionSelect.add(new Option('Choose a session', ''));
            sessions.forEach(session => {
                const state = session.checkin_open ? 'Check-in open' : 'Outside check-in window';
                elements.sessionSelect.add(new Option(`${session.title} · ${state}`, String(session.id)));
            });
            elements.sessionSelect.disabled = false;
            elements.sessionSelect.value = sessions.some(session => String(session.id) === previousId)
                ? previousId
                : '';
            updateSelectedSession();
            if (!elements.sessionSelect.value) {
                showStatus('Choose an assigned session to continue.');
            }
        } catch (error) {
            if (error.status === 401) {
                accessToken = null;
                sessionStorage.removeItem('attendance-token');
                sessionStorage.removeItem('attendance-name');
                try {
                    await stopCamera();
                } catch (stopError) {
                    console.error('Could not stop the camera after the sign-in expired.', stopError);
                }
                showLogin();
                showStatus(error.message, 'error');
            } else {
                showStatus(error.message || 'Could not load sessions. Check your connection and try again.', 'error');
            }
        } finally {
            elements.refreshButton.disabled = false;
        }
    }

    async function signIn(event) {
        event.preventDefault();
        elements.loginButton.disabled = true;
        showStatus('Signing in…');
        try {
            const result = await apiRequest('login.php', {
                method: 'POST',
                body: {
                    email: elements.email.value.trim(),
                    password: elements.password.value
                }
            });
            if (typeof result.token !== 'string' || !/^[a-f0-9]{64}$/.test(result.token)) {
                throw new Error('The server returned an invalid sign-in response.');
            }
            accessToken = result.token;
            const name = typeof result.user?.name === 'string' ? result.user.name : 'Attendance Holder';
            sessionStorage.setItem('attendance-token', accessToken);
            sessionStorage.setItem('attendance-name', name);
            elements.password.value = '';
            showAttendance(name);
            await loadSessions();
        } catch (error) {
            showStatus(error.message || 'Could not sign in. Check your connection and try again.', 'error');
        } finally {
            elements.loginButton.disabled = false;
        }
    }

    async function startCamera() {
        const session = selectedSession();
        if (!session || !session.checkin_open) {
            showStatus('This session is outside its check-in window.', 'error');
            return;
        }
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            showStatus('Camera access requires HTTPS. Open the secure website address and try again.', 'error');
            return;
        }
        if (typeof window.Html5Qrcode !== 'function') {
            showStatus('The QR scanner could not load. Check your connection and reload the app.', 'error');
            return;
        }

        elements.startScanButton.disabled = true;
        showStatus('Starting camera…');
        try {
            scanner = new window.Html5Qrcode('qr-reader', {verbose: false});
            elements.qrReader.hidden = false;
            await scanner.start(
                {facingMode: 'environment'},
                {fps: 10, qrbox: {width: 240, height: 240}},
                value => {
                    void submitQrCode(value, session.id);
                },
                () => {}
            );
            scannerRunning = true;
            elements.startScanButton.hidden = true;
            elements.stopScanButton.hidden = false;
            showStatus('Camera ready. Point it at a member QR code.');
        } catch (error) {
            scanner = null;
            scannerRunning = false;
            elements.qrReader.hidden = true;
            showStatus(error.message || 'Camera could not start. Allow camera access and try again.', 'error');
        } finally {
            elements.startScanButton.disabled = false;
        }
    }

    async function submitQrCode(rawValue, sessionId) {
        if (scanPending || selectedSession()?.id !== sessionId) return;
        scanPending = true;
        const qrToken = rawValue.trim();
        if (!/^[a-fA-F0-9]{32}$/.test(qrToken)) {
            showStatus('This is not a valid St. Raguel member QR code.', 'error');
            window.setTimeout(() => {
                scanPending = false;
            }, 1200);
            return;
        }
        showStatus('Checking member QR code…');
        try {
            const result = await apiRequest('scan.php', {
                method: 'POST',
                body: {session_id: sessionId, qr_token: qrToken}
            });
            if (!result.member || typeof result.member.name !== 'string' || !result.member.name.trim()) {
                throw new Error('The server returned an invalid check-in response.');
            }
            showStatus(`Checked in: ${result.member.name}`, 'success');
            if ('vibrate' in navigator) navigator.vibrate(100);
            void loadRoster(sessionId);
        } catch (error) {
            showStatus(error.message || 'Could not save this check-in. Try again.', 'error');
            if (error.status === 401) {
                await signOut();
            }
        } finally {
            window.setTimeout(() => {
                scanPending = false;
            }, 1200);
        }
    }

    async function signOut() {
        elements.signOutButton.disabled = true;
        let cameraWarning = '';
        try {
            await stopCamera();
        } catch {
            cameraWarning = ' The camera could not be stopped cleanly.';
        }
        let message = 'Signed out.';
        try {
            if (accessToken) {
                await apiRequest('logout.php', {method: 'POST', body: {}});
            }
        } catch {
            message = 'Signed out on this device. The server token could not be revoked; it expires automatically.';
        } finally {
            accessToken = null;
            sessions = [];
            rosterRequest++;
            elements.memberList.replaceChildren();
            sessionStorage.removeItem('attendance-token');
            sessionStorage.removeItem('attendance-name');
            showLogin();
            showStatus(message + cameraWarning, cameraWarning ? 'error' : 'info');
            elements.signOutButton.disabled = false;
        }
    }

    elements.loginForm.addEventListener('submit', signIn);
    elements.sessionSelect.addEventListener('change', updateSelectedSession);
    elements.refreshButton.addEventListener('click', loadSessions);
    elements.rosterSearch.addEventListener('input', () => {
        const query = elements.rosterSearch.value.trim().toLocaleLowerCase();
        elements.memberList.querySelectorAll('.member-card').forEach(card => {
            card.hidden = !card.dataset.memberName.includes(query);
        });
    });
    elements.startScanButton.addEventListener('click', startCamera);
    elements.stopScanButton.addEventListener('click', () => {
        void stopCamera()
            .then(() => showStatus('Camera stopped.'))
            .catch(() => showStatus('Could not stop the camera. Close this page and try again.', 'error'));
    });
    elements.signOutButton.addEventListener('click', signOut);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && scannerRunning) {
            void stopCamera()
                .then(() => showStatus('Camera stopped while the app is in the background.'))
                .catch(error => {
                    console.error('Could not stop the background camera stream.', error);
                    showStatus('Could not stop the camera while the app is in the background.', 'error');
                });
        }
    });

    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        installPrompt = event;
        elements.installButton.hidden = false;
    });
    elements.installButton.addEventListener('click', async () => {
        if (!installPrompt) return;
        await installPrompt.prompt();
        installPrompt = null;
        elements.installButton.hidden = true;
    });
    window.addEventListener('appinstalled', () => {
        installPrompt = null;
        elements.installButton.hidden = true;
        showStatus('St. Raguel Attendance was installed on this device.', 'success');
    });

    if ('serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('attendance-app-sw.js').catch(() => {
            showStatus('The app could not enable install support. You can still use attendance in this browser.', 'error');
        });
    }

    if (accessToken && /^[a-f0-9]{64}$/.test(accessToken)) {
        showAttendance(sessionStorage.getItem('attendance-name'));
        void loadSessions();
    } else {
        accessToken = null;
        sessionStorage.removeItem('attendance-token');
        sessionStorage.removeItem('attendance-name');
        showLogin();
    }
})();
