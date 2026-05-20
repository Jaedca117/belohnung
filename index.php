<?php
/**
 * Einfaches Belohnungsbarometer für Kinder
 * ------------------------------------------------------------
 * Installation:
 * 1. Diese Datei als index.php auf dein Webhosting laden.
 * 2. Zwei Bilder daneben legen, z.B. kind1.jpg und kind2.jpg.
 * 3. Namen und Bilddateien unten in $children anpassen.
 * 4. Im Browser öffnen, Vollbild aktivieren, fertig.
 *
 * Hinweise:
 * - Keine Datenbank nötig.
 * - Der Stand wird im Browser gespeichert per localStorage.
 * - Auf einem anderen Gerät/Browser ist der Stand daher separat.
 */

$children = [
    [
        'id'    => 'sophie',
        'name'  => 'Sophie',
        'photo' => 'kind1.jpg',
    ],
    [
        'id'    => 'max',
        'name'  => 'Maximilian',
        'photo' => 'kind2.jpg',
    ],
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#111827">
    <title>Belohnungsbarometer</title>
    <style>
        :root {
            --bg: #111827;
            --text: #ffffff;
            --muted: rgba(255, 255, 255, 0.75);
            --panel-shadow: rgba(0, 0, 0, 0.35);
            --button-bg: rgba(255, 255, 255, 0.18);
            --button-bg-hover: rgba(255, 255, 255, 0.28);
            --button-border: rgba(255, 255, 255, 0.30);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.55), rgba(0,0,0,0));
            pointer-events: none;
        }

        .topbar h1 {
            margin: 0;
            font-size: clamp(1.1rem, 2vw, 1.7rem);
            text-shadow: 0 2px 8px rgba(0,0,0,0.45);
            pointer-events: auto;
        }

        .topbar-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
            pointer-events: auto;
        }

        button {
            border: 1px solid var(--button-border);
            background: var(--button-bg);
            color: white;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }

        button:hover,
        button:focus-visible {
            background: var(--button-bg-hover);
            outline: none;
        }

        .app {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            height: 100%;
        }

        .app[data-layout="single"] {
            grid-template-columns: 1fr;
        }

        .child-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: 74px 24px 24px;
            isolation: isolate;
            cursor: pointer;
            transition: background 250ms ease, transform 120ms ease, filter 250ms ease;
            user-select: none;
        }

        .child-panel:active {
            transform: scale(0.992);
        }

        .child-panel + .child-panel {
            border-left: 4px solid rgba(255, 255, 255, 0.25);
        }

        .child-panel.is-hidden {
            display: none;
        }

        .child-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: radial-gradient(circle at center, rgba(255,255,255,0.20), rgba(0,0,0,0.10) 55%, rgba(0,0,0,0.28));
        }

        .photo-wrap {
            width: clamp(130px, 20vw, 260px);
            aspect-ratio: 1 / 1;
            border-radius: 999px;
            padding: clamp(6px, 1vw, 12px);
            background: rgba(255, 255, 255, 0.38);
            box-shadow: 0 18px 45px var(--panel-shadow);
            margin-bottom: clamp(16px, 3vh, 34px);
        }

        .photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
            display: block;
            background: rgba(255,255,255,0.25);
        }

        .name {
            margin: 0 0 10px;
            font-size: clamp(2rem, 5vw, 5rem);
            line-height: 1;
            text-align: center;
            text-shadow: 0 4px 18px rgba(0,0,0,0.35);
        }

        .emoji {
            font-size: clamp(5rem, 14vw, 13rem);
            line-height: 1;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,0.32));
            margin: 6px 0;
        }

        .level-text {
            margin-top: 10px;
            font-size: clamp(1.25rem, 2.8vw, 2.4rem);
            font-weight: 900;
            text-align: center;
            text-shadow: 0 3px 14px rgba(0,0,0,0.30);
        }

        .hint {
            margin-top: 8px;
            color: var(--muted);
            font-size: clamp(0.95rem, 1.3vw, 1.15rem);
            text-align: center;
            text-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }

        .progress {
            width: min(72%, 440px);
            height: clamp(18px, 2.8vh, 32px);
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.22);
            border: 2px solid rgba(255,255,255,0.30);
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.24);
            margin-top: clamp(16px, 3vh, 32px);
        }

        .progress-fill {
            height: 100%;
            width: 10%;
            background: rgba(255,255,255,0.75);
            border-radius: inherit;
            transition: width 220ms ease;
        }

        .panel-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: clamp(16px, 3vh, 28px);
        }

        .panel-actions button {
            font-size: clamp(0.9rem, 1.4vw, 1.05rem);
            padding: 9px 13px;
        }

        .rotate-button {
            position: absolute;
            right: 14px;
            bottom: 14px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            padding: 0;
            font-size: 1.35rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
        }

        .child-panel.is-rotated {
            transform: rotate(180deg);
        }

        .child-panel.is-rotated:active {
            transform: rotate(180deg) scale(0.992);
        }

        .danger-pulse {
            animation: dangerPulse 650ms ease-in-out 2;
        }

        @keyframes dangerPulse {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.22) saturate(1.25); }
        }

        .shake {
            animation: shake 360ms ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-10px); }
            40% { transform: translateX(10px); }
            60% { transform: translateX(-7px); }
            80% { transform: translateX(7px); }
        }

        @media (max-width: 760px) {
            .topbar {
                align-items: flex-start;
                gap: 8px;
                padding: 8px 10px;
            }

            .topbar h1 {
                font-size: 0.95rem;
            }

            .topbar-actions {
                gap: 6px;
            }

            button {
                padding: 8px 10px;
                font-size: 0.82rem;
            }

            .app {
                width: 100%;
                height: 100dvh;
            }

            .child-panel {
                padding: 52px 12px 12px;
            }

            .child-panel + .child-panel {
                border-left: 0;
                border-top: 3px solid rgba(255, 255, 255, 0.25);
            }

            .photo-wrap {
                margin-bottom: 8px;
            }

            .level-text {
                margin-top: 6px;
            }

            .panel-actions {
                margin-top: 8px;
                gap: 8px;
            }

            .panel-actions button {
                padding: 7px 10px;
            }

            .hint {
                display: none;
            }

            .progress {
                width: 88%;
                margin-top: 10px;
            }

            .rotate-button {
                right: 8px;
                bottom: 8px;
                width: 38px;
                height: 38px;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 760px) and (orientation: portrait) {
            .app {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 1fr;
            }

            .photo-wrap {
                width: clamp(66px, 12vh, 100px);
            }

            .name {
                margin-bottom: 6px;
                font-size: clamp(1.2rem, 3.5vh, 1.8rem);
            }

            .emoji {
                font-size: clamp(2.2rem, 5.6vh, 3.8rem);
            }

            .level-text {
                font-size: clamp(0.88rem, 2.3vh, 1.2rem);
            }

            .progress {
                height: 14px;
            }
        }

        @media (max-width: 760px) and (orientation: landscape) {
            .app {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 1fr;
            }

            .child-panel {
                justify-content: flex-start;
                overflow-y: auto;
                padding: 44px 10px max(10px, env(safe-area-inset-bottom));
            }

            .child-panel + .child-panel {
                border-top: 0;
                border-left: 3px solid rgba(255, 255, 255, 0.25);
            }

            .photo-wrap {
                width: clamp(58px, 17vh, 90px);
            }

            .name {
                margin-bottom: 5px;
                font-size: clamp(1rem, 4.2vh, 1.5rem);
            }

            .emoji {
                font-size: clamp(2rem, 6vh, 3.2rem);
                margin: 2px 0;
            }

            .level-text {
                font-size: clamp(0.8rem, 2.4vh, 1.1rem);
            }

            .progress {
                height: 12px;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <h1>Belohnungsbarometer</h1>
        <div class="topbar-actions">
            <button type="button" id="viewToggleButton">Ansicht: Beide</button>
            <button type="button" id="fullscreenButton">Vollbild</button>
            <button type="button" id="resetAllButton">Alle zurücksetzen</button>
        </div>
    </header>

    <main class="app" aria-label="Belohnungsbarometer für zwei Kinder">
        <?php foreach ($children as $child): ?>
            <section class="child-panel" data-child-id="<?= e($child['id']) ?>" role="button" tabindex="0" aria-label="<?= e($child['name']) ?> einen Schritt weiterstellen">
                <div class="photo-wrap">
                    <img src="<?= e($child['photo']) ?>" alt="Foto von <?= e($child['name']) ?>" draggable="false">
                </div>

                <h2 class="name"><?= e($child['name']) ?></h2>
                <div class="emoji" aria-hidden="true">😊</div>
                <div class="level-text">Alles gut</div>
                <div class="hint">Tippen/klicken = ein Schritt weiter</div>

                <div class="progress" aria-hidden="true">
                    <div class="progress-fill"></div>
                </div>

                <div class="panel-actions">
                    <button type="button" data-action="undo">Zurück</button>
                    <button type="button" data-action="reset">Reset</button>
                </div>

                <button type="button" class="rotate-button" data-action="rotate" aria-label="Ausrichtung drehen">↻</button>
            </section>
        <?php endforeach; ?>
    </main>

    <script>
        const children = <?= json_encode($children, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const minLevel = 1;
        const maxLevel = 10;

        const levels = {
            1: { emoji: '😊', text: 'Alles gut',              color: '#16a34a' },
            2: { emoji: '😊', text: 'Weiter so',             color: '#22c55e' },
            3: { emoji: '🙂', text: 'Noch im grünen Bereich', color: '#84cc16' },
            4: { emoji: '😐', text: 'Achtung',               color: '#eab308' },
            5: { emoji: '😐', text: 'Es wird kritisch',      color: '#f59e0b' },
            6: { emoji: '😕', text: 'Bitte ändern',          color: '#f97316' },
            7: { emoji: '🙁', text: 'Nicht gut',             color: '#ef4444' },
            8: { emoji: '☹️', text: 'Fast geschafft',        color: '#dc2626' },
            9: { emoji: '😢', text: 'Letzte Chance',         color: '#b91c1c' },
            10:{ emoji: '😭', text: 'Durchgefallen',         color: '#7f1d1d' },
        };

        let audioContext = null;

        function getAudioContext() {
            if (!audioContext) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                audioContext = new AudioContextClass();
            }
            return audioContext;
        }

        function playTone(frequency = 620, duration = 0.14, type = 'sine', volume = 0.08) {
            const ctx = getAudioContext();
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();

            oscillator.type = type;
            oscillator.frequency.value = frequency;

            gain.gain.setValueAtTime(0.0001, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(volume, ctx.currentTime + 0.015);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration);

            oscillator.connect(gain);
            gain.connect(ctx.destination);

            oscillator.start(ctx.currentTime);
            oscillator.stop(ctx.currentTime + duration + 0.02);
        }

        function playWarningSound() {
            playTone(700, 0.12, 'square', 0.06);
            setTimeout(() => playTone(520, 0.12, 'square', 0.06), 130);
        }

        function playFailSound() {
            playTone(220, 0.22, 'sawtooth', 0.11);
            setTimeout(() => playTone(165, 0.28, 'sawtooth', 0.11), 230);
            setTimeout(() => playTone(110, 0.42, 'sawtooth', 0.12), 540);
        }

        function storageKey(childId) {
            return `reward-barometer-level-${childId}`;
        }

        function rotateStorageKey(childId) {
            return `reward-barometer-rotated-${childId}`;
        }

        function viewStorageKey() {
            return 'reward-barometer-view';
        }

        function getSavedView() {
            const savedView = localStorage.getItem(viewStorageKey()) || 'all';
            const validViews = ['all', ...children.map(child => child.id)];
            return validViews.includes(savedView) ? savedView : 'all';
        }

        function saveView(view) {
            localStorage.setItem(viewStorageKey(), view);
        }

        function isRotated(childId) {
            return localStorage.getItem(rotateStorageKey(childId)) === "1";
        }

        function setRotated(childId, rotated) {
            localStorage.setItem(rotateStorageKey(childId), rotated ? "1" : "0");
            renderChild(childId);
        }

        function toggleRotated(childId) {
            setRotated(childId, !isRotated(childId));
            playTone(600, 0.08, "triangle", 0.045);
        }

        function getLevel(childId) {
            const saved = Number(localStorage.getItem(storageKey(childId)));
            if (Number.isInteger(saved) && saved >= minLevel && saved <= maxLevel) {
                return saved;
            }
            return minLevel;
        }

        function setLevel(childId, level) {
            const cleanLevel = Math.min(maxLevel, Math.max(minLevel, level));
            localStorage.setItem(storageKey(childId), String(cleanLevel));
            renderChild(childId);
        }

        function renderChild(childId) {
            const panel = document.querySelector(`[data-child-id="${childId}"]`);
            if (!panel) return;

            const level = getLevel(childId);
            const config = levels[level];

            panel.style.background = config.color;
            panel.querySelector('.emoji').textContent = config.emoji;
            panel.querySelector('.level-text').textContent = `${config.text} · ${level}/${maxLevel}`;
            panel.querySelector('.progress-fill').style.width = `${(level / maxLevel) * 100}%`;
            panel.setAttribute('aria-label', `${panel.querySelector('.name').textContent}: Stufe ${level} von ${maxLevel}`);
            panel.classList.toggle('is-rotated', isRotated(childId));
        }

        function increase(childId) {
            const current = getLevel(childId);
            const next = Math.min(maxLevel, current + 1);
            setLevel(childId, next);

            const panel = document.querySelector(`[data-child-id="${childId}"]`);

            if (next >= maxLevel) {
                playFailSound();
                panel.classList.remove('danger-pulse', 'shake');
                void panel.offsetWidth;
                panel.classList.add('danger-pulse', 'shake');
            } else {
                playWarningSound();
            }
        }

        function decrease(childId) {
            setLevel(childId, getLevel(childId) - 1);
            playTone(440, 0.10, 'sine', 0.05);
        }

        function reset(childId) {
            setLevel(childId, minLevel);
            playTone(880, 0.10, 'sine', 0.045);
        }

        function resetAll() {
            children.forEach(child => setLevel(child.id, minLevel));
            playTone(880, 0.10, 'sine', 0.045);
            setTimeout(() => playTone(1040, 0.10, 'sine', 0.045), 120);
        }

        function getViewButtonLabel(activeView) {
            if (activeView === 'all') return 'Ansicht: Beide';
            const child = children.find(item => item.id === activeView);
            return child ? `Ansicht: ${child.name}` : 'Ansicht: Beide';
        }

        function applyView(activeView) {
            const app = document.querySelector('.app');
            const panels = document.querySelectorAll('.child-panel');
            const isSingle = activeView !== 'all';

            app.dataset.layout = isSingle ? 'single' : 'all';

            panels.forEach(panel => {
                const shouldShow = activeView === 'all' || panel.dataset.childId === activeView;
                panel.classList.toggle('is-hidden', !shouldShow);
            });

            document.getElementById('viewToggleButton').textContent = getViewButtonLabel(activeView);
        }

        function cycleView() {
            const views = ['all', ...children.map(child => child.id)];
            const currentView = getSavedView();
            const currentIndex = views.indexOf(currentView);
            const nextView = views[(currentIndex + 1) % views.length];
            saveView(nextView);
            applyView(nextView);
            playTone(520, 0.08, 'triangle', 0.04);
        }

        document.querySelectorAll('.child-panel').forEach(panel => {
            const childId = panel.dataset.childId;

            panel.addEventListener('click', event => {
                const actionButton = event.target.closest('button[data-action]');

                if (actionButton) {
                    event.stopPropagation();
                    const action = actionButton.dataset.action;
                    if (action === 'undo') decrease(childId);
                    if (action === 'reset') reset(childId);
                    if (action === 'rotate') toggleRotated(childId);
                    return;
                }

                increase(childId);
            });

            panel.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    increase(childId);
                }
            });
        });

        document.getElementById('resetAllButton').addEventListener('click', resetAll);
        document.getElementById('viewToggleButton').addEventListener('click', cycleView);

        document.getElementById('fullscreenButton').addEventListener('click', async () => {
            try {
                if (!document.fullscreenElement) {
                    await document.documentElement.requestFullscreen();
                } else {
                    await document.exitFullscreen();
                }
            } catch (error) {
                alert('Vollbild konnte nicht aktiviert werden. Der Browser blockiert das eventuell.');
            }
        });

        children.forEach(child => renderChild(child.id));
        applyView(getSavedView());
    </script>
</body>
</html>
