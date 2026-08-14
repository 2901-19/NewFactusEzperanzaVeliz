const { app, BrowserWindow, dialog, Menu, shell } = require('electron');
const { spawn, spawnSync } = require('node:child_process');
const path = require('node:path');
const fs = require('node:fs');
const http = require('node:http');

const config = require('./config.json');

const PORT = config.port || 8000;
const BASE_URL = `http://127.0.0.1:${PORT}`;
const SMOKE = process.env.FACTUS_SMOKE === '1';

const EXTENSIONES = ['curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_pgsql', 'pgsql', 'sqlite3', 'zip'];

let mainWindow = null;
let phpProc = null;
let cerrando = false;

function appDir() {
    if (config.appPath && fs.existsSync(config.appPath)) {
        return config.appPath;
    }
    if (app.isPackaged) {
        return path.join(path.dirname(app.getAppPath()), 'app');
    }
    return path.join(__dirname, '..');
}

function phpDir() {
    if (config.phpPath && fs.existsSync(config.phpPath)) {
        return config.phpPath;
    }
    if (app.isPackaged) {
        return path.join(path.dirname(app.getAppPath()), 'php');
    }
    return null;
}

function phpBin() {
    const dir = phpDir();
    return dir ? path.join(dir, 'php.exe') : 'php';
}

function escribirIniPortable() {
    const dir = phpDir();
    if (!dir) {
        return null;
    }
    const ext = path.join(dir, 'ext');
    const iniPath = path.join(app.getPath('userData'), 'factus-php.ini');
    const lineas = [
        'memory_limit=256M',
        'upload_max_filesize=20M',
        'post_max_size=20M',
        'max_execution_time=120',
        'date.timezone=America/Caracas',
        'variables_order=EGPCS',
        'display_errors=0',
        'log_errors=1',
        `extension_dir=${ext}`,
        ...EXTENSIONES.map((e) => `extension=${e}`),
    ];
    fs.writeFileSync(iniPath, lineas.join('\r\n') + '\r\n', 'utf8');
    return iniPath;
}

function startServer() {
    return new Promise((resolve, reject) => {
        const env = { ...process.env };
        const iniPath = escribirIniPortable();
        if (iniPath) {
            env.PHPRC = iniPath;
        }
        const appRoot = appDir();
        const args = [
            '-d',
            'variables_order=EGPCS',
            '-S',
            `127.0.0.1:${PORT}`,
            '-t',
            path.join(appRoot, 'public'),
            path.join(appRoot, 'public', 'index.php'),
        ];
        const child = spawn(phpBin(), args, {
            cwd: appRoot,
            windowsHide: true,
            stdio: ['ignore', 'pipe', 'pipe'],
            env,
        });
        child.stdout.on('data', (d) => console.log('[php]', d.toString().trim()));
        child.stderr.on('data', (d) => console.error('[php]', d.toString().trim()));
        child.on('error', (err) => reject(err));
        child.on('exit', (code) => {
            console.log('[php] proceso finalizado (codigo', code + ')');
            phpProc = null;
            if (!cerrando && !SMOKE && mainWindow && !mainWindow.isDestroyed()) {
                dialog
                    .showMessageBox(mainWindow, {
                        type: 'error',
                        title: 'FACTUS',
                        message: 'El servidor del sistema se detuvo.',
                        detail: 'Puedes reintentar arrancarlo o salir.',
                        buttons: ['Reintentar', 'Salir'],
                        defaultId: 0,
                        cancelId: 1,
                    })
                    .then(({ response }) => {
                        if (response === 0) {
                            startServer().catch(() => app.quit());
                        } else {
                            app.quit();
                        }
                    });
            }
        });
        phpProc = child;
        resolve();
    });
}

function puertoLibre() {
    return new Promise((resolve) => {
        const req = http.get(`${BASE_URL}/`, (res) => {
            res.resume();
            resolve(false);
        });
        req.on('error', () => resolve(true));
        req.setTimeout(1500, () => {
            req.destroy();
            resolve(true);
        });
    });
}

function esperarServidor(timeoutMs = 20000) {
    return new Promise((resolve, reject) => {
        const inicio = Date.now();
        const tick = () => {
            const req = http.get(`${BASE_URL}/`, (res) => {
                res.resume();
                resolve(res.statusCode);
            });
            req.on('error', () => {
                if (Date.now() - inicio > timeoutMs) {
                    reject(new Error('El servidor tardó demasiado en responder.'));
                } else {
                    setTimeout(tick, 300);
                }
            });
        };
        tick();
    });
}

function iconPath() {
    const p = path.join(__dirname, 'build', 'icon.ico');
    return fs.existsSync(p) ? p : undefined;
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        show: false,
        autoHideMenuBar: true,
        icon: iconPath(),
        title: 'FACTUS',
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: true,
        },
    });
    mainWindow.setMenuBarVisibility(false);
    Menu.setApplicationMenu(null);
    mainWindow.once('ready-to-show', () => {
        if (config.fullscreen) {
            mainWindow.setFullScreen(true);
        }
        mainWindow.show();
    });
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        if (/^https?:\/\//.test(url)) {
            shell.openExternal(url);
        }
        return { action: 'deny' };
    });
    mainWindow.webContents.on('did-fail-load', (_e, code, desc) => {
        if (code === -3) {
            return;
        }
        console.error('[renderer] fallo de carga:', code, desc);
    });
    mainWindow.webContents.on('before-input-event', (event, input) => {
        if (input.type === 'keyDown' && input.key === 'F11') {
            mainWindow.setFullScreen(!mainWindow.isFullScreen());
            event.preventDefault();
        }
    });
    mainWindow.on('closed', () => {
        mainWindow = null;
    });
    mainWindow.loadURL(`${BASE_URL}/`);
}

function recargar() {
    if (mainWindow && !mainWindow.isDestroyed()) {
        mainWindow.loadURL(`${BASE_URL}/`);
    }
}

async function pedirBaseDeDatos() {
    const { response } = await dialog.showMessageBox(mainWindow, {
        type: 'warning',
        title: 'FACTUS',
        message: 'El sistema no pudo conectarse a la base de datos.',
        detail: 'Verifica que PostgreSQL esté en ejecución y que la base de datos exista.',
        buttons: ['Reintentar', 'Salir'],
        defaultId: 0,
        cancelId: 1,
    });
    return response === 0;
}

async function main() {
    if (!(await puertoLibre())) {
        dialog.showErrorBox('FACTUS', `El puerto ${PORT} ya está en uso. Cierra la otra instancia de FACTUS o la aplicación que lo ocupa.`);
        app.quit();
        return;
    }
    try {
        await startServer();
    } catch {
        dialog.showErrorBox('FACTUS', 'No se pudo iniciar PHP. Verifica la instalación de FACTUS.');
        app.quit();
        return;
    }
    if (SMOKE) {
        try {
            await esperarServidor();
            console.log('[smoke] servidor listo en', BASE_URL);
        } catch (err) {
            console.error('[smoke] el servidor no respondio:', err.message);
        }
        if (process.env.FACTUS_SMOKE_WINDOW === '1') {
            createWindow();
        }
        setTimeout(() => app.quit(), 4000);
        return;
    }
    try {
        const status = await esperarServidor();
        createWindow();
        if (status >= 500) {
            if (await pedirBaseDeDatos()) {
                recargar();
            } else {
                app.quit();
            }
        }
    } catch (err) {
        dialog.showErrorBox('FACTUS', err.message);
        app.quit();
    }
}

function matarPhp() {
    if (!phpProc) {
        return;
    }
    const pid = phpProc.pid;
    phpProc = null;
    if (process.platform === 'win32') {
        spawnSync('taskkill', ['/pid', String(pid), '/T', '/F'], { windowsHide: true });
    } else {
        try {
            process.kill(pid, 'SIGTERM');
        } catch {
            // proceso ya muerto
        }
    }
}

const lock = app.requestSingleInstanceLock();
if (!lock) {
    app.quit();
} else {
    app.setName('FACTUS');
    app.on('second-instance', () => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) {
                mainWindow.restore();
            }
            mainWindow.focus();
        }
    });
    app.on('window-all-closed', () => app.quit());
    app.on('before-quit', () => {
        cerrando = true;
        matarPhp();
    });
    app.whenReady().then(main);
}
