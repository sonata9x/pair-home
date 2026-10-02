(function(root,factory){
  if(typeof module==='object'&&module.exports)module.exports=factory();
  else root.PairHomeGrid=factory();
})(typeof window!=='undefined'?window:this,function(){
  'use strict';
  var columns=30,rows=30;
  function clamp(n,min,max){return Math.max(min,Math.min(max,n));}
  function number(n,fallback){n=Number(n);return Number.isFinite(n)?n:fallback;}
  function snap(widget){
    var w=clamp(Math.round(number(widget.w,3)),1,columns);
    var h=clamp(Math.round(number(widget.h,3)),1,rows);
    return {x:clamp(Math.round(number(widget.x,0)),0,columns-w),y:clamp(Math.round(number(widget.y,0)),0,rows-h),w:w,h:h};
  }
  function overlaps(a,b){return a.x<b.x+b.w&&a.x+a.w>b.x&&a.y<b.y+b.h&&a.y+a.h>b.y;}
  function available(box,widgets,id){return !widgets.some(function(w){return w.type!=='sticker'&&w.id!==id&&overlaps(box,snap(w));});}
  function firstSpace(widget,widgets){
    var box=snap(widget);
    for(var y=0;y<=rows-box.h;y++)for(var x=0;x<=columns-box.w;x++){
      var candidate={x:x,y:y,w:box.w,h:box.h};
      if(available(candidate,widgets,widget.id))return candidate;
    }
    return null;
  }
  function move(start,dx,dy){return snap({x:start.x+Math.round(dx),y:start.y+Math.round(dy),w:start.w,h:start.h});}
  function resize(start,dx,dy){return {x:start.x,y:start.y,w:clamp(start.w+Math.round(dx),1,columns-start.x),h:clamp(start.h+Math.round(dy),1,rows-start.y)};}
  function ratioUnits(value){
    var parts=String(value||'free').split(':'),w=Math.round(number(parts[0],0)),h=Math.round(number(parts[1],0));
    return w>0&&h>0?{w:w,h:h}:null;
  }
  function resizeRatio(start,targetW,targetH,axis,ratio){
    var box=snap(start),units=ratioUnits(ratio);if(!units)return resize(box,targetW-box.w,targetH-box.h);
    var maxScale=Math.floor(Math.min((columns-box.x)/units.w,(rows-box.y)/units.h));if(maxScale<1)return null;
    var scale;
    if(axis==='width')scale=Math.round(number(targetW,box.w)/units.w);
    else if(axis==='height')scale=Math.round(number(targetH,box.h)/units.h);
    else{
      var widthDelta=Math.abs(number(targetW,box.w)-box.w)/units.w,heightDelta=Math.abs(number(targetH,box.h)-box.h)/units.h;
      scale=widthDelta>=heightDelta?Math.round(number(targetW,box.w)/units.w):Math.round(number(targetH,box.h)/units.h);
    }
    scale=clamp(scale,1,maxScale);
    return {x:box.x,y:box.y,w:units.w*scale,h:units.h*scale};
  }
  return {columns:columns,rows:rows,snap:snap,available:available,firstSpace:firstSpace,move:move,resize:resize,ratioUnits:ratioUnits,resizeRatio:resizeRatio};
});
