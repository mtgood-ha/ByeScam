const BS_CONFIG = {
    apiEndpoint: 'http://localhost/ByeScam/api/scan.php',
    scanInterval: 2000
};

// 1. Inject UI Structure if it doesn't already exist
function bs_injectUI() {
    if (document.getElementById('byescam-ext-root')) return;

    const root = document.createElement('div');
    root.id = 'byescam-ext-root';
    // Inside bs_injectUI() in content.js
    root.innerHTML = `
    <div class="bs-card" id="bs-card">
        <div class="bs-header" id="bs-drag">
            <img src="${chrome.runtime.getURL('icon.png')}" class="bs-icon" alt="ByeScam">
            <div class="bs-title-wrap">
                <span class="bs-dot" id="bs-status-dot"></span>
                <p class="bs-title" id="bs-status-text">Ready to scan</p>
            </div>
            <div class="bs-toggle" id="bs-toggle">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </div>
        </div>
        <div class="bs-body" id="bs-details">
            <div class="bs-safe">Open an email to analyze potential threats.</div>
        </div>
    </div>
`   ;
    document.body.appendChild(root);
    bs_initListeners();
}

// 2. Bind UI Interactivity (Drag & Toggle)
function bs_initListeners() {
    const dragHandle = document.getElementById('bs-drag');
    const card = document.getElementById('bs-card');
    const toggleBtn = document.getElementById('bs-toggle');

    if (!dragHandle || !card || !toggleBtn) return;

    // Handle Expand/Collapse
    toggleBtn.onclick = () => {
        card.classList.toggle('expanded');
    };

    // Drag and Drop Physics
    let isDragging = false, startX, startY, currentX = 0, currentY = 0;

    dragHandle.onmousedown = (e) => {
        if (e.target.closest('#bs-toggle')) return;
        isDragging = true;
        startX = e.clientX - currentX;
        startY = e.clientY - currentY;
    };

    document.onmousemove = (e) => {
        if (!isDragging) return;
        e.preventDefault();
        currentX = e.clientX - startX;
        currentY = e.clientY - startY;
        card.style.transform = `translate(${currentX}px, ${currentY}px)`;
    };

    document.onmouseup = () => {
        isDragging = false;
    };
}

// 3. Gmail DOM Extractor
function bs_extractEmailData() {
    const bodyEl = document.querySelector('.a3s.aiL');
    const senderEl = document.querySelector('.gD');
    
    if (!bodyEl) return null;

    const firstLinkEl = bodyEl.querySelector('a');
    return {
        message: bodyEl.innerText.substring(0, 1500),
        sender: senderEl ? senderEl.getAttribute('email') : '',
        url: firstLinkEl ? firstLinkEl.href : ''
    };
}

// 4. Main Controller & Polling Loop
let lastEmailHash = '';

function bs_startPolling() {
    setInterval(async () => {
        bs_injectUI(); // Ensure widget persists if Gmail updates DOM

        const data = bs_extractEmailData();
        const statusDot = document.getElementById('bs-status-dot');
        const statusText = document.getElementById('bs-status-text');
        const detailsWrap = document.getElementById('bs-details');

        if (!data || !statusText) return;

        const currentHash = data.message + data.sender;
        if (currentHash === lastEmailHash) return;
        lastEmailHash = currentHash;

        statusDot.className = 'bs-dot';
        statusText.innerText = 'Analyzing...';

        try {
            const res = await fetch(BS_CONFIG.apiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            statusDot.className = `bs-dot ${result.level}`;
            statusText.innerText = `Risk: ${result.risk}%`;

            if (result.flags && result.flags.length > 0) {
                detailsWrap.innerHTML = `<strong>Category: ${result.category}</strong><br>` + 
                    result.flags.map(f => `<div class="bs-flag">• ${f.label}</div>`).join('');
            } else {
                detailsWrap.innerHTML = `<div class="bs-safe">No obvious threats detected.</div>`;
            }

        } catch (e) {
            statusText.innerText = 'Offline';
            detailsWrap.innerHTML = `<div>Cannot reach ByeScam local server. Ensure XAMPP is running.</div>`;
        }
    }, BS_CONFIG.scanInterval);
}

// Initialize on load
bs_injectUI();
bs_startPolling();