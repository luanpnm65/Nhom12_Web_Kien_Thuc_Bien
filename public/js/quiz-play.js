(() => {
    const byId = (id) => document.getElementById(id);
    const run = byId('quizRun');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const startButton = byId('startQuizEarlyBtn');
    const answerButtons = [...run.querySelectorAll('.kahoot-btn')];
    const answerLabels = answerButtons.map((_, index) => byId(`optText${index}`));
    const connectionStatus = byId('quizConnectionStatus');
    const readingClock = byId('readingTimeText');
    const readingProgress = byId('readingProgressBar');
    const questionClock = byId('questionTimerDisplay');
    const questionProgress = byId('questionProgressBar');
    const points = new Intl.NumberFormat('vi-VN');

    let state = JSON.parse(byId('quizRunState').textContent);
    let syncedAt = performance.getEntriesByType('navigation')[0]?.responseStart || performance.now();
    let pending = false;
    let retryAt = null;
    let needsReload = false;

    function remainingMilliseconds() {
        return Math.max(0, state.deadline - state.server_now - (performance.now() - syncedAt));
    }

    function updateButtons() {
        const locked = pending || retryAt !== null || needsReload || remainingMilliseconds() <= 0;
        startButton.disabled = locked || state.phase !== 'reading';
        answerButtons.forEach((button, index) => {
            button.disabled = locked || state.phase !== 'question' || index >= state.question.options.length;
        });
    }

    function renderTimer(remaining) {
        if (state.phase === 'reading') {
            const seconds = Math.ceil(remaining / 1000);
            const minutesText = String(Math.floor(seconds / 60)).padStart(2, '0');
            const secondsText = String(seconds % 60).padStart(2, '0');
            readingClock.textContent = `${minutesText}:${secondsText}`;
            readingProgress.style.width = `${Math.min(100, remaining / 180000 * 100)}%`;
        } else if (state.question) {
            const limit = state.question.time_limit;
            const seconds = state.feedback
                ? Math.max(0, limit - state.feedback.time_taken)
                : Math.min(limit, remaining / 1000);
            questionClock.textContent = seconds.toFixed(1);
            questionProgress.style.width = `${seconds / limit * 100}%`;
        }
    }

    function renderState() {
        const { phase, question, feedback } = state;
        byId('readingPhaseContainer').style.display = phase === 'reading' ? '' : 'none';
        byId('kahootPhaseContainer').style.display = question ? 'flex' : 'none';
        byId('quizCompletionBox').style.display = phase === 'complete' ? '' : 'none';

        if (question) {
            byId('qCurrentIndex').textContent = question.number;
            byId('kahootQuestionText').textContent = question.text;
            byId('liveScoreDisplay').textContent = points.format(state.score);
            answerButtons.forEach((button, index) => {
                const isCorrect = feedback?.correct_option_index === index;
                answerLabels[index].textContent = question.options[index] ?? '';
                button.style.opacity = feedback && !isCorrect ? '0.32' : '1';
                button.style.outline = isCorrect ? '4px solid #ffffff' : 'none';
            });

            const banner = byId('instantFeedbackBanner');
            banner.style.display = feedback ? 'block' : 'none';
            if (feedback) {
                if (feedback.is_correct) {
                    banner.style.background = 'rgba(34, 197, 94, 0.25)';
                    banner.style.border = '1px solid #4ade80';
                    banner.style.color = '#4ade80';
                    banner.textContent = `Chính xác! +${points.format(feedback.points_earned)} điểm (${Number(feedback.time_taken).toFixed(1)} giây)`;
                } else {
                    banner.style.background = 'rgba(239, 68, 68, 0.25)';
                    banner.style.border = '1px solid #f87171';
                    banner.style.color = '#fca5a5';
                    const message = feedback.selected_option_index === null ? 'Hết giờ!' : 'Chưa chính xác!';
                    banner.textContent = `${message} Đáp án đúng là ${question.options[feedback.correct_option_index]}.`;
                }
            }
        }

        if (phase === 'complete') {
            byId('finalScoreValue').textContent = `${points.format(state.score)} điểm`;
            byId('finalAccuracyValue').textContent = `${state.correct_count} / ${state.total_questions}`;
            byId('quizSaveStatus').textContent = `Đã lưu kết quả. Điểm cộng vào bảng xếp hạng là ${points.format(state.ranking_points)}.`;
        }

        renderTimer(remainingMilliseconds());
        updateButtons();
    }

    function showConnectionStatus(message) {
        connectionStatus.textContent = message;
        connectionStatus.hidden = !message;
    }

    async function requestState(url = run.dataset.stateUrl, body) {
        if (pending || needsReload || state.phase === 'complete') return;

        pending = true;
        updateButtons();
        const sentAt = performance.now();
        const controller = new AbortController();
        const requestTimeout = setTimeout(() => controller.abort(), 8000);
        try {
            const options = { cache: 'no-store', headers: { Accept: 'application/json' }, signal: controller.signal };
            if (body !== undefined) {
                options.method = 'POST';
                options.headers['Content-Type'] = 'application/json';
                options.headers['X-CSRF-TOKEN'] = csrfToken;
                options.body = JSON.stringify(body);
                options.keepalive = true;
            }

            const response = await fetch(url, options);
            if ([401, 403, 404, 419].includes(response.status)) {
                needsReload = true;
                showConnectionStatus('Không thể tiếp tục phiên hiện tại. Hãy tải lại trang để đồng bộ lượt làm.');
                return;
            }
            if (!response.ok) throw new Error(`Quiz state request failed (${response.status})`);

            state = await response.json();
            syncedAt = (sentAt + performance.now()) / 2;
            retryAt = null;
            showConnectionStatus('');
            renderState();
        } catch {
            retryAt = performance.now() + 1500;
            showConnectionStatus('Kết nối bị gián đoạn. Đang đồng bộ lượt làm bài…');
        } finally {
            clearTimeout(requestTimeout);
            pending = false;
            updateButtons();
        }
    }

    function tick() {
        if (state.phase === 'complete' || needsReload) {
            clearInterval(clockInterval);
            return;
        }

        const remaining = remainingMilliseconds();
        renderTimer(remaining);
        if (retryAt === null ? remaining <= 0 : performance.now() >= retryAt) {
            requestState();
        }
    }

    startButton.addEventListener('click', () => requestState(run.dataset.startUrl, {}));
    answerButtons.forEach((button, selectedIndex) => {
        button.addEventListener('click', () => requestState(run.dataset.answerUrl, {
            question_id: state.question.id,
            selected_option_index: selectedIndex,
        }));
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') requestState();
    });
    window.addEventListener('online', () => requestState());
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) requestState();
    });

    renderState();
    const clockInterval = setInterval(tick, 100);
    tick();
})();
