const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(require('path').join(__dirname, '../../js/pair-home.js'), 'utf8');
const context = vm.createContext({});
vm.runInContext(source.slice(source.indexOf('  function clamp('), source.indexOf('  function isDirty(')), context);
let cases = 0;
for (const rect of [{width:1056,height:688},{width:314,height:440},{width:240,height:330}]) {
  for (const type of ['image','text','sticker']) {
    for (const rotation of [-180,-90,-30,-2,0,30,90,180]) {
      for (const position of [-20,0,50,96,120]) {
        for (const ratio of ['1:1','9:16','16:9']) {
          const widget = {type,x:position,y:position,w:96,h:96,rotation,data:{ratio,aspect:.2}};
          const original = JSON.stringify(widget);
          const g = context.widgetGeometry(widget,rect);
          assert.equal(JSON.stringify(widget),original,'viewport rendering must not mutate stored layout');
          const a = rotation*Math.PI/180;
          const width = g.w*rect.width/100, height=g.h*rect.height/100;
          const halfX=(width*Math.abs(Math.cos(a))+height*Math.abs(Math.sin(a)))/2;
          const halfY=(width*Math.abs(Math.sin(a))+height*Math.abs(Math.cos(a)))/2;
          const cx=g.x*rect.width/100+width/2,cy=g.y*rect.height/100+height/2;
          assert(cx-halfX>=-1e-7 && cx+halfX<=rect.width+1e-7,'rotated horizontal bounds');
          assert(cy-halfY>=-1e-7 && cy+halfY<=rect.height+1e-7,'rotated vertical bounds');
          assert(g.x>=-1e-7 && g.y>=-1e-7 && g.w<=96+1e-7 && g.h<=96+1e-7,'server-compatible dimensions');
          cases++;
        }
      }
    }
  }
}
console.log(`${cases} rotated layout and viewport cases passed`);
