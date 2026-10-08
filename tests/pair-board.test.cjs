const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');

const galleryList = read('skin/board/pair_gallery/list.skin.php');
const boardStyle = read('css/pair-board.css');
const galleryStyle = read('skin/board/pair_gallery/style.css');
const logList = read('skin/board/pair_log/list.skin.php');
const logView = read('skin/board/pair_log/view.skin.php');
const logStyle = read('skin/board/pair_log/style.css');
const logHead = read('skin/board/pair_log/write_update.head.skin.php');

assert(galleryList.includes('pair-gallery-dialog'), 'gallery must include a centered lightbox');
assert(boardStyle.includes('width: clamp(720px,100%,960px)') && boardStyle.includes('min-width: 720px'), 'boards must share the main 960px shell and 720px minimum width');
assert(boardStyle.includes('--pair-board-page-image') && boardStyle.includes('.pair-board::before'), 'boards must inherit the main page backdrop');
assert(galleryList.includes('pair_board_style_attribute'), 'gallery must receive the shared main design variables');
assert(logList.includes('pair_board_style_attribute') && logView.includes('pair_board_style_attribute'), 'log list and detail must receive the shared main design variables');
assert(galleryList.includes('pair-gallery-overlay'), 'gallery must include a hover information overlay');
assert(!galleryList.includes('profile'), 'gallery must not reserve a profile column');
assert(galleryStyle.includes('grid-auto-flow:dense'), 'gallery must use dense variable-ratio placement');
assert(logStyle.includes('aspect-ratio:16/9'), 'log list cards must crop to 16:9');
assert(logStyle.includes('.pair-log-session-card img{display:block;width:100%;height:auto;object-fit:contain}'), 'detail session card must keep its original ratio');
assert(logList.includes("$item['wr_1']") && logList.includes("$item['wr_2']") && logList.includes("$item['wr_3']"), 'log list must expose writer, playtime and date');
assert(!logList.includes("$item['wr_5']"), 'log list must not expose overview');
assert(logView.includes('sandbox="allow-scripts allow-popups"'), 'log HTML must render in a sandbox');
assert(logView.includes('pair-log-overview') && logView.includes('pair-log-waiting'), 'detail must support overview and waiting BGM');
assert(logHead.includes("$w === 'u'") && logHead.includes('SELECT wr_subject,wr_content,wr_1,wr_2,wr_3,wr_7,wr_8'), 'settings updates must preserve the immutable HTML body and list metadata');

console.log('pair-board static tests passed');
