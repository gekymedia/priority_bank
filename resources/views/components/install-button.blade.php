<div class="fixed bottom-4 right-4 z-50" id="installContainer" style="display: none;">
    <button id="installButton" class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clip-rule="evenodd" />
        </svg>
        Install App
    </button>
</div>

<script>
    let deferredPrompt;
    const installButton = document.getElementById('installButton');
    const installContainer = document.getElementById('installContainer');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installContainer.style.display = 'block';
    });

    installButton.addEventListener('click', () => {
        installContainer.style.display = 'none';
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted install prompt');
            } else {
                console.log('User dismissed install prompt');
            }
            deferredPrompt = null;
        });
    });

    window.addEventListener('appinstalled', () => {
        installContainer.style.display = 'none';
        deferredPrompt = null;
    });
</script>