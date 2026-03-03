<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>⚓ 船长探索 · 动态人生 · 可折叠</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000;
            overflow: hidden;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            color: white;
            width: 100vw; height: 100vh;
            position: fixed; touch-action: none;
        }
        #canvas {
            display: block;
            width: 100%; height: 100%;
            background: black;
            position: absolute; top: 0; left: 0;
            z-index: 1; cursor: default;
        }
        .foreground {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 2;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; pointer-events: none;
            text-shadow: 0 0 30px rgba(0,0,0,0.9);
            padding: 20px;
            backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);
        }
        .caption {
            font-size: clamp(1.2rem, 5vw, 2.2rem);
            font-weight: 400;
            color: rgba(255,255,255,0.95);
            background: rgba(20,20,30,0.3);
            padding: 0.3rem 2rem;
            border-radius: 50px;
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255,240,180,0.2);
            display: inline-block; margin-bottom: 0.5rem;
            font-family: 'Courier New', monospace; letter-spacing: 1px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }
        .years-main {
            font-size: clamp(3.5rem, 16vw, 8rem);
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 20%, #ffe68f 80%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(255,200,50,0.6);
            filter: drop-shadow(0 4px 8px black);
            line-height: 1.2; margin-bottom: 0.2rem;
        }
        .detail {
            font-size: clamp(1.1rem, 5vw, 2.4rem);
            font-weight: 300;
            color: rgba(255,255,255,0.85);
            background: rgba(20,20,30,0.3);
            padding: 0.3rem 1.8rem;
            border-radius: 50px;
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            border: 1px solid rgba(255,240,180,0.2);
            display: inline-block;
            font-family: 'Courier New', monospace; letter-spacing: 2px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
            transition: all 0.15s ease;
        }
        .detail.update-flash {
            color: #fff; background: rgba(255,200,100,0.3); border-color: #ffcc66; transform: scale(1.02);
        }

        /* 顶部栏 — 可折叠，保留原始渐变风格 */
        .top-bar {
            position: absolute; top: 0; left: 0; width: 100%;
            z-index: 3; pointer-events: none;
            padding: calc(12px + env(safe-area-inset-top, 0px)) 16px 16px 16px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.3));
            backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
            border-bottom: 1px solid rgba(255,200,100,0.25);
            display: flex; flex-direction: column; align-items: center;
            gap: 16px; transition: all 0.3s ease;
        }
        .top-bar.collapsed {
            padding-bottom: 8px; gap: 4px;
        }
        .top-bar.collapsed .birth-picker {
            display: none;
        }

        /* 进度条容器 — 内置折叠按钮 */
        .progress-container {
            width: 90%; max-width: 500px;
            display: flex; align-items: center; gap: 12px;
            background: rgba(0,0,0,0.5);
            padding: 8px 8px 8px 16px;
            border-radius: 60px;
            border: 1px solid rgba(255,200,100,0.2);
            backdrop-filter: blur(2px);
            pointer-events: auto;
            transition: all 0.3s;
        }
        progress {
            flex: 1; height: 12px; border-radius: 20px; background-color: #3a3a1a; border: none; overflow: hidden;
            -webkit-appearance: none; appearance: none;
        }
        progress::-webkit-progress-bar { background-color: #3a3a1a; border-radius: 20px; }
        progress::-webkit-progress-value { background: linear-gradient(90deg, #ffb347, #ffe68f); border-radius: 20px; box-shadow: 0 0 15px #ffaa33; }
        .progress-text {
            font-size: 0.9rem; font-family: 'Courier New', monospace; color: #ffdb8e;
            min-width: 65px; text-align: right; font-weight: 500; white-space: nowrap;
        }

        /* 折叠按钮 — 圆形，位于进度条最右侧 */
        .collapse-btn {
            background: rgba(255,200,100,0.2);
            border: 1px solid #ffb347;
            color: #ffdb8e;
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; cursor: pointer; pointer-events: auto;
            transition: 0.2s; box-shadow: 0 0 15px rgba(255,180,0,0.3);
            line-height: 1; user-select: none; flex-shrink: 0;
            margin-left: 4px;
        }
        .collapse-btn:hover { background: rgba(255,200,100,0.4); transform: scale(1.05); }

        /* 生日选择器 — 公历 */
        .birth-picker {
            width: 100%; max-width: 500px;
            background: rgba(10, 10, 15, 0.6);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 200, 100, 0.3);
            border-radius: 48px;
            padding: 14px 16px;
            pointer-events: auto;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
        }
        .solar-panel {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 12px;
        }
        .solar-panel input[type="date"] {
            background: #2a2a2a;
            border: 1px solid #ffb347;
            color: white;
            padding: 8px 16px;
            border-radius: 40px;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            cursor: pointer; outline: none;
            flex: 1 1 200px;
        }
        .btn {
            background: #5f4f2e;
            border: 1px solid #ffcc66;
            color: white;
            padding: 8px 24px;
            border-radius: 40px;
            font-size: 1rem; font-weight: 500;
            cursor: pointer; transition: all 0.1s;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        .btn:hover { background: #7f693a; border-color: #ffdb8e; transform: scale(1.02); }
        .btn:active { transform: scale(0.98); }

        @media (max-width: 600px) {
            .caption { font-size: 1.1rem; padding: 0.2rem 1.2rem; }
            .years-main { font-size: 4rem; }
            .detail { font-size: 1.2rem; padding: 0.2rem 1.2rem; }
            .top-bar { padding: calc(8px + env(safe-area-inset-top,0px)) 12px 8px; }
            .progress-container { width: 100%; padding: 6px 6px 6px 12px; }
            .collapse-btn { width: 32px; height: 32px; font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <canvas id="canvas"></canvas>
    <div class="foreground">
        <div class="caption">⚓ 船长在这个世界已经探索了</div>
        <div class="years-main" id="yearsDisplay">28年</div>
        <div class="detail" id="detailDisplay">84天 16小时 32分钟 10秒</div>
    </div>

    <div class="top-bar" id="topBar">
        <!-- 进度条 + 折叠按钮 集成在同一行 -->
        <div class="progress-container">
            <progress id="lifeProgress" value="0" max="1"></progress>
            <span class="progress-text" id="progressPercent">0%</span>
            <div class="collapse-btn" id="collapseToggle">▼</div>
        </div>

        <!-- 公历生日选择器 -->
        <div class="birth-picker" id="birthPicker">
            <div class="solar-panel">
                <input type="date" id="solarDatePicker" value="1998-01-09">
                <button class="btn" id="applySolarBtn">✓ 设定公历</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // ---------- 固定参数 ----------
            const totalWeeksGoal = 4160;      // 80岁对应的总周数
            const totalPoints = totalWeeksGoal;
            const emojis = ['🧧', '🚗', '🏠', '👶', '💼', '🎓', '❤️', '🌟', '💰', '🍀'];

            // 默认生日 (1998-01-09)
            let birth = new Date('1998-01-09T00:00:00+08:00');

            // 全局变量
            let points = [];
            let currentWeeks = 0;
            let cutoff = 0;
            let mouseX = null, mouseY = null, mouseActive = false;
            const repulsionStrength = 25;
            const interactionRadius = 60;
            const emojiHoldDuration = 2000;

            // ---------- 核心：日常年龄计算 ----------
            function computeCurrentData() {
                const now = new Date();

                let years = now.getFullYear() - birth.getFullYear();
                const monthDiff = now.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && now.getDate() < birth.getDate())) {
                    years--;
                }

                const lastBirthday = new Date(now.getFullYear(), birth.getMonth(), birth.getDate(), 0, 0, 0);
                if (lastBirthday > now) {
                    lastBirthday.setFullYear(lastBirthday.getFullYear() - 1);
                }

                const msSinceLastBirthday = now - lastBirthday;
                const totalSecondsSinceLastBirthday = Math.floor(msSinceLastBirthday / 1000);
                const daysSinceLastBirthday = Math.floor(totalSecondsSinceLastBirthday / 86400);
                const secondsToday = totalSecondsSinceLastBirthday % 86400;
                const hours = Math.floor(secondsToday / 3600);
                const minutes = Math.floor((secondsToday % 3600) / 60);
                const seconds = secondsToday % 60;

                const livedMs = now - birth;
                const livedWeeks = Math.floor(livedMs / (7 * 86400000));
                const displayWeeks = Math.min(livedWeeks, totalWeeksGoal);
                const progress = displayWeeks / totalWeeksGoal;

                return {
                    years,
                    daysSinceLastBirthday,
                    hours,
                    minutes,
                    seconds,
                    displayWeeks,
                    progress
                };
            }

            // ---------- 更新UI（年份、进度条，并重新计算cutoff）----------
            function updateMainDisplay() {
                const data = computeCurrentData();
                currentWeeks = data.displayWeeks;
                document.getElementById('yearsDisplay').innerText = data.years + '年';
                document.getElementById('lifeProgress').value = data.progress;
                document.getElementById('progressPercent').innerText = Math.round(data.progress * 100) + '%';

                if (points.length > 0) {
                    cutoff = getCutoff(points, currentWeeks);
                }
            }

            function updateDetailDisplay() {
                const data = computeCurrentData();
                const detailElem = document.getElementById('detailDisplay');
                const newText = `${data.daysSinceLastBirthday}天 ${data.hours}小时 ${data.minutes}分钟 ${data.seconds}秒`;
                if (detailElem.innerText !== newText) {
                    detailElem.innerText = newText;
                    detailElem.classList.add('update-flash');
                    setTimeout(() => detailElem.classList.remove('update-flash'), 200);
                }
            }

            // ---------- 随机数生成器 (Mulberry32) ----------
            function mulberry32(seed) {
                return function() {
                    seed |= 0;
                    seed = (seed + 0x6D2B79F5) | 0;
                    let t = Math.imul(seed ^ seed >>> 15, 1 | seed);
                    t = (t + Math.imul(t ^ t >>> 7, 61 | t)) ^ t;
                    return ((t ^ t >>> 14) >>> 0) / 4294967296;
                };
            }

            // ---------- 生成点阵 ----------
            function generatePoints(seed, count) {
                const rng = mulberry32(seed);
                const points = [];
                for (let i = 0; i < count; i++) {
                    const isSurprise = rng() < 0.05;
                    let emojiIndex = 0;
                    if (isSurprise) {
                        emojiIndex = Math.floor(rng() * emojis.length);
                    }
                    points.push({
                        baseX: rng(),
                        baseY: rng(),
                        priority: rng(),
                        phaseX: rng() * Math.PI * 2,
                        phaseY: rng() * Math.PI * 2,
                        freqX: 0.3 + rng() * 0.7,
                        freqY: 0.3 + rng() * 0.7,
                        breathPhase: rng() * Math.PI * 2,
                        offsetX: 0, offsetY: 0, vx: 0, vy: 0,
                        surprise: isSurprise,
                        emojiIndex: emojiIndex,
                        emojiUntil: 0
                    });
                }
                return points;
            }

            // ---------- 计算点亮阈值 ----------
            function getCutoff(points, n) {
                if (n <= 0) return -0.1;
                if (n >= points.length) return 2.0;
                const priorities = points.map(p => p.priority);
                priorities.sort((a, b) => a - b);
                return priorities[n - 1];
            }

            // ---------- 根据当前birth重新生成点阵 ----------
            function rebuildFromBirth() {
                let y = birth.getFullYear(), m = birth.getMonth()+1, d = birth.getDate();
                let seed = y * 10000 + m * 100 + d;
                points = generatePoints(seed, totalPoints);
                updateMainDisplay(); // 这会重新计算cutoff
            }

            // ---------- 绘制Canvas（灰色/绿色比例动态变化）----------
            function drawCanvas(points, cutoff, time, mouseX, mouseY, mouseActive) {
                const canvas = document.getElementById('canvas');
                if (!canvas) return;

                const containerWidth = canvas.clientWidth;
                const containerHeight = canvas.clientHeight;
                const dpr = window.devicePixelRatio || 1;
                canvas.width = containerWidth * dpr;
                canvas.height = containerHeight * dpr;

                const ctx = canvas.getContext('2d');
                ctx.scale(dpr, dpr);
                ctx.clearRect(0, 0, containerWidth, containerHeight);

                const ampX = containerWidth * 0.015;
                const ampY = containerHeight * 0.015;
                const pointRadius = Math.max(1.0, Math.min(2.1, containerWidth / 585));
                const surpriseRadius = pointRadius * 2.5;
                const nowTime = Date.now();

                // 交互斥力
                if (mouseActive && mouseX !== null && mouseY !== null) {
                    for (let p of points) {
                        if (p.priority > cutoff) {
                            let baseScreenX = p.baseX * containerWidth;
                            let yRatio = p.baseY * 0.7;
                            let floatX = Math.sin(time * p.freqX + p.phaseX) * ampX;
                            let floatY = Math.cos(time * p.freqY + p.phaseY) * ampY;
                            let currentX = baseScreenX + floatX + p.offsetX;
                            let currentY = containerHeight * yRatio + floatY + p.offsetY;

                            let dx = currentX - mouseX;
                            let dy = currentY - mouseY;
                            let dist = Math.sqrt(dx*dx + dy*dy);
                            if (dist < interactionRadius && dist > 0.1) {
                                let force = (1 - dist / interactionRadius) * repulsionStrength;
                                let angle = Math.atan2(dy, dx);
                                p.vx += Math.cos(angle) * force;
                                p.vy += Math.sin(angle) * force;
                            }
                        }
                    }
                }

                // 更新物理状态
                for (let p of points) {
                    if (p.priority > cutoff) {
                        p.vx *= 0.92;
                        p.vy *= 0.92;
                        p.offsetX += p.vx;
                        p.offsetY += p.vy;
                        p.offsetX *= 0.98;
                        p.offsetY *= 0.98;
                    } else {
                        p.vx = 0; p.vy = 0; p.offsetX = 0; p.offsetY = 0;
                    }
                }

                // 表情触发
                if (mouseActive && mouseX !== null && mouseY !== null) {
                    const activeCount = points.filter(p => p.emojiUntil > nowTime).length;
                    if (activeCount === 0) {
                        for (let p of points) {
                            if (p.priority > cutoff && p.surprise) {
                                let baseScreenX = p.baseX * containerWidth;
                                let yRatio = p.baseY * 0.7;
                                let floatX = Math.sin(time * p.freqX + p.phaseX) * ampX;
                                let floatY = Math.cos(time * p.freqY + p.phaseY) * ampY;
                                let x = baseScreenX + floatX + p.offsetX;
                                let y = containerHeight * yRatio + floatY + p.offsetY;

                                let dx = x - mouseX;
                                let dy = y - mouseY;
                                let dist = Math.sqrt(dx*dx + dy*dy);
                                if (dist < interactionRadius) {
                                    p.emojiUntil = nowTime + emojiHoldDuration;
                                    break;
                                }
                            }
                        }
                    }
                }

                // 绘制所有点
                for (let p of points) {
                    const isRemaining = p.priority > cutoff;
                    let x, y;
                    if (isRemaining) {
                        let baseScreenX = p.baseX * containerWidth;
                        let yRatio = p.baseY * 0.7;
                        let floatX = Math.sin(time * p.freqX + p.phaseX) * ampX;
                        let floatY = Math.cos(time * p.freqY + p.phaseY) * ampY;
                        x = baseScreenX + floatX + p.offsetX;
                        y = containerHeight * yRatio + floatY + p.offsetY;
                    } else {
                        let baseX = p.baseX * containerWidth;
                        let baseYRatio = 0.7 + 0.3 * p.baseY;
                        let wave = 0.02 * Math.sin(time * 0.8 + p.breathPhase);
                        let yRatio = Math.min(1.0, Math.max(0.7, baseYRatio + wave));
                        y = containerHeight * yRatio;
                        x = baseX;
                    }

                    let breath = 0.8 + 0.2 * Math.sin(time * 1.5 + p.breathPhase);
                    const isSurprise = isRemaining && p.surprise;

                    if (isSurprise && p.emojiUntil <= nowTime) {
                        // 金色闪烁点
                        let flicker = 0.7 + 0.3 * Math.sin(time * 20 + p.breathPhase);
                        let lightness = 70 + 20 * flicker;
                        ctx.fillStyle = `hsl(50, 100%, ${lightness}%)`;
                        ctx.shadowColor = `hsl(50, 100%, 70%)`;
                        ctx.shadowBlur = 20 + 10 * flicker;
                        ctx.beginPath(); ctx.arc(x, y, surpriseRadius, 0, Math.PI * 2); ctx.fill();
                        continue;
                    }
                    if (isSurprise) continue; // 表情点稍后绘制

                    if (isRemaining) {
                        // 剩余点：亮绿色
                        let lightness = 55 + 30 * breath;
                        ctx.fillStyle = `hsl(120, 80%, ${lightness}%)`;
                        ctx.shadowColor = `hsl(120, 80%, 60%)`;
                        ctx.shadowBlur = 12;
                        ctx.beginPath(); ctx.arc(x, y, pointRadius, 0, Math.PI * 2); ctx.fill();
                    } else {
                        // 已度过点：白灰色
                        let lightness = 35 + 15 * breath;
                        ctx.fillStyle = `hsl(0, 0%, ${lightness}%)`;
                        ctx.shadowColor = 'transparent';
                        ctx.shadowBlur = 0;
                        ctx.beginPath(); ctx.arc(x, y, pointRadius, 0, Math.PI * 2); ctx.fill();
                    }
                }

                // 绘制激活的表情
                for (let p of points) {
                    const isRemaining = p.priority > cutoff;
                    if (!isRemaining || !p.surprise || p.emojiUntil <= nowTime) continue;
                    let baseScreenX = p.baseX * containerWidth;
                    let yRatio = p.baseY * 0.7;
                    let floatX = Math.sin(time * p.freqX + p.phaseX) * ampX;
                    let floatY = Math.cos(time * p.freqY + p.phaseY) * ampY;
                    let x = baseScreenX + floatX + p.offsetX;
                    let y = containerHeight * yRatio + floatY + p.offsetY;
                    const emojiFontSize = Math.max(24, containerWidth / 22);
                    ctx.font = `${emojiFontSize}px 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', system-ui, sans-serif`;
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.shadowColor = `hsl(45, 90%, 70%)`;
                    ctx.shadowBlur = 25;
                    ctx.fillStyle = '#fff';
                    ctx.fillText(emojis[p.emojiIndex], x, y);
                }
                ctx.shadowBlur = 0; ctx.shadowColor = 'transparent';
            }

            // ---------- 事件监听 ----------
            function initEvents(canvas) {
                canvas.addEventListener('mousemove', (e) => {
                    const rect = canvas.getBoundingClientRect();
                    mouseX = e.clientX - rect.left;
                    mouseY = e.clientY - rect.top;
                    mouseActive = true;
                });
                canvas.addEventListener('mouseleave', () => {
                    mouseActive = false; mouseX = mouseY = null;
                });
                canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    const rect = canvas.getBoundingClientRect();
                    if (e.touches.length > 0) {
                        mouseX = e.touches[0].clientX - rect.left;
                        mouseY = e.touches[0].clientY - rect.top;
                        mouseActive = true;
                    }
                });
                canvas.addEventListener('touchend', () => {
                    mouseActive = false; mouseX = mouseY = null;
                });
                canvas.addEventListener('touchcancel', () => {
                    mouseActive = false; mouseX = mouseY = null;
                });
            }

            // ---------- 折叠功能 ----------
            const topBar = document.getElementById('topBar');
            const collapseBtn = document.getElementById('collapseToggle');
            collapseBtn.addEventListener('click', () => {
                topBar.classList.toggle('collapsed');
                collapseBtn.innerText = topBar.classList.contains('collapsed') ? '▲' : '▼';
            });

            // ---------- 公历设定 ----------
            document.getElementById('applySolarBtn').addEventListener('click', ()=>{
                let newDateStr = document.getElementById('solarDatePicker').value;
                if (newDateStr) {
                    let newBirth = new Date(newDateStr + 'T00:00:00+08:00');
                    if (!isNaN(newBirth)) {
                        birth = newBirth;
                        rebuildFromBirth(); // 重新生成点阵，并更新cutoff
                    }
                }
            });

            // ---------- 初始化 ----------
            function initVisual() {
                const canvas = document.getElementById('canvas');
                rebuildFromBirth();            // 生成点阵并更新显示
                updateDetailDisplay();

                initEvents(canvas);

                let startTime = null;
                function animate(now) {
                    if (!startTime) startTime = now;
                    const elapsed = (now - startTime) / 500;
                    drawCanvas(points, cutoff, elapsed, mouseX, mouseY, mouseActive);
                    requestAnimationFrame(animate);
                }
                requestAnimationFrame(animate);

                // 每分钟更新一次主显示（确保进度条和cutoff与时间同步）
                setInterval(() => {
                    const oldWeeks = currentWeeks;
                    const newData = computeCurrentData();
                    if (newData.displayWeeks !== oldWeeks) {
                        updateMainDisplay();
                    }
                }, 60000);

                // 每秒更新详细时间
                setInterval(updateDetailDisplay, 1000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initVisual);
            } else {
                initVisual();
            }
        })();
    </script>
</body>
</html>
