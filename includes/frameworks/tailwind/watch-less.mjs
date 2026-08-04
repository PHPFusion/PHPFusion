import {watch} from 'node:fs';
import {spawn} from 'node:child_process';
import {fileURLToPath} from 'node:url';

const directory = fileURLToPath(new URL('.', import.meta.url));
const sources = new Set(['tailwind.less', 'tailwind.input.less']);
const npmCommand = process.platform === 'win32' ? 'npm.cmd' : 'npm';

let timer = null;
let building = false;
let rebuildQueued = false;

function build() {
    if (building) {
        rebuildQueued = true;
        return;
    }

    building = true;
    const child = spawn(npmCommand, ['run', 'build:tailwind:framework'], {
        cwd: fileURLToPath(new URL('../../..', import.meta.url)),
        stdio: 'inherit',
    });

    child.on('exit', () => {
        building = false;
        if (rebuildQueued) {
            rebuildQueued = false;
            build();
        }
    });
}

watch(directory, {persistent: true}, (_eventType, filename) => {
    if (!filename || !sources.has(filename.toString())) {
        return;
    }

    clearTimeout(timer);
    timer = setTimeout(build, 120);
});

console.log('Watching tailwind.less and tailwind.input.less...');
