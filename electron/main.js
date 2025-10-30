import { app, BrowserWindow } from 'electron';
import path from 'path';
import { spawn } from 'child_process';
import http from 'http';

const LARAVEL_URL = 'http://127.0.0.1:8000';

function checkServer(url) {
  return new Promise((resolve) => {
    const req = http.get(url, () => {
      resolve(true);
    });
    req.on('error', () => {
      resolve(false);
    });
    req.end();
  });
}

async function startLaravelServer() {
  const isRunning = await checkServer(LARAVEL_URL);

  if (!isRunning) {
    console.log('Laravel server not running. Starting php artisan serve...');
    // Adjust path to your PHP executable and Laravel root
    const laravelProcess = spawn('php', ['artisan', 'serve'], {
      cwd: path.resolve('./'), // Laravel root folder
      shell: true,
      stdio: 'inherit',
    });

    // Optional: kill Laravel when Electron closes
    app.on('before-quit', () => {
      laravelProcess.kill();
    });

    // Wait a few seconds for server to start
    await new Promise((res) => setTimeout(res, 5000));
  } else {
    console.log('Laravel server is already running.');
  }
}

function createWindow () {
  const win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  win.loadURL('http://127.0.0.1:8000/projects'); // Laravel server URL
}

app.whenReady().then(async () => {
  await startLaravelServer();
  createWindow();

  app.on('activate', function () {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
