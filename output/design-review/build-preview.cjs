const fs = require('fs');
const path = require('path');
const root = path.resolve(__dirname, '../..');
let main = fs.readFileSync(path.join(root, 'main.php'), 'utf8');
let markup = main.slice(main.indexOf('<div class="pair-home-shell"'), main.indexOf('<script type="application/json"'));
markup = markup.replace(/<\?php for[\s\S]*?<\?php } \?>/, '<i></i>'.repeat(7)).replace(/<\?php echo \$pair_frame_theme; \?>/, 'diary').replace(/<\?php[\s\S]*?\?>/g, '');
markup = markup.replace(/^[ \t]+$/gm, '');
const widget = (id,type,x,y,w,h,data,rotation=0) => ({id,type,x,y,w,h,z:3,rotation,data});
const art = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="750" viewBox="0 0 600 750"><rect width="600" height="750" fill="#e3e8e1"/><circle cx="410" cy="215" r="96" fill="#faf7ed"/><path d="M0 540Q160 350 320 540T640 490V750H0Z" fill="#acbcae"/><path d="M0 650Q230 490 600 650V750H0Z" fill="#7d9588"/><text x="42" y="65" fill="#526b60" font-family="Georgia" font-size="22">a place for the two of us.</text></svg>';
const widgets = [
 widget('title','text',7,8,70,12,{text:'Our little archive',font:'serif',font_size:48,color:'#454b43',line_height:1.1}),
 widget('subtitle','text',7,21,80,7,{text:'너와 나의 계절을 모아두는 작은 공간',font:'sans',font_size:14,color:'#7a8176',line_height:1.5}),
 widget('portrait','image',7,34,26,48,{src:'data:image/svg+xml,'+encodeURIComponent(art),ratio:'3:4',shape:'rounded',radius:8},-2),
 widget('date','dday',42,36,25,25,{title:'함께 쌓아가는 날들',date:'2025-03-14',mode:'since',include_today:true,show_date:true,style:'lcd',accent:'#829782'}),
 widget('links','category',72,36,22,48,{title:'OUR PAGES',style:'buttons',accent:'#829782',items:[{label:'Profile',url:'#profile'},{label:'Diary',url:'#diary'},{label:'Gallery',url:'#gallery'}]}),
 widget('music','bgm',42,67,25,18,{title:'우리의 플레이리스트',src:'sample-tone.wav',loop:true,volume:.2}),
 widget('caption','text',7,90,80,5,{text:'TWO OF US  /  EVERY LITTLE MOMENT',font:'sans',font_size:11,color:'#8a8d80'})
];
const config = {admin:true,backgroundColor:'#EAECE6',backgroundImage:'',frameTheme:'diary',widgets};
const html = '<!doctype html><html lang="ko"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>페어홈 디자인 검토 · 샘플 데이터</title><link rel="stylesheet" href="../../css/default.css.php"><link rel="stylesheet" href="../../css/ra0-content.css"><link rel="stylesheet" href="../../css/pair-home.css"><link rel="stylesheet" href="../../css/pair-home-editor.css"><body>'+markup+'<script type="application/json" id="pair-home-bootstrap">'+JSON.stringify(config)+'</script><script src="../../js/pair-home.js"></script></body></html>';
fs.writeFileSync(path.join(__dirname,'preview.html'), html);
// A quiet, local test tone; never autoplayed and never uploaded.
const samples=8000*8, wav=Buffer.alloc(44+samples*2);
wav.write('RIFF');wav.writeUInt32LE(wav.length-8,4);wav.write('WAVEfmt ',8);wav.writeUInt32LE(16,16);
wav.writeUInt16LE(1,20);wav.writeUInt16LE(1,22);wav.writeUInt32LE(8000,24);wav.writeUInt32LE(16000,28);wav.writeUInt16LE(2,32);wav.writeUInt16LE(16,34);wav.write('data',36);wav.writeUInt32LE(samples*2,40);
for(let i=0;i<samples;i++)wav.writeInt16LE(Math.round(500*Math.sin(2*Math.PI*220*i/8000)),44+i*2);
fs.writeFileSync(path.join(__dirname,'sample-tone.wav'),wav);
