(function(){
  'use strict';
  var bootstrap=document.getElementById('pair-home-bootstrap');
  var shell=document.getElementById('pair-home-shell');
  var canvas=document.getElementById('pair-home-canvas');
  if(!bootstrap||!shell||!canvas)return;
  var config=JSON.parse(bootstrap.textContent||'{}');
  var state={editing:false,selectedId:null,backgroundColor:config.backgroundColor||'#F2ECE5',backgroundImage:config.backgroundImage||'',widgets:Array.isArray(config.widgets)?config.widgets:[]};
  var interaction=null,activeUploadWidgetId=null,statusTimer=null;
  function clamp(v,min,max){return Math.max(min,Math.min(max,v));}
  function byId(id){return state.widgets.find(function(w){return w.id===id;});}
  function newId(type){return type+'-'+Date.now().toString(36)+'-'+Math.random().toString(36).slice(2,7);}
  function safeLink(url){url=String(url||'').trim();return /^(?:javascript|data|vbscript):/i.test(url)?'':url;}
  function navigate(url){url=safeLink(url);if(!url||state.editing)return;if(/^https?:\/\//i.test(url)){window.open(url,'_blank','noopener');return;}if(window.parent&&window.parent!==window)window.parent.postMessage({type:'navigate',url:url},'*');else window.location.href=url;}
  function applyBackground(){shell.style.setProperty('--pair-bg',state.backgroundColor);shell.style.backgroundImage=state.backgroundImage?'url("'+state.backgroundImage.replace(/"/g,'%22')+'")':'none';}
  function ddayText(widget){var target=new Date(widget.data.date+'T00:00:00'),today=new Date();today.setHours(0,0,0,0);var days=Math.round((today-target)/86400000);if(widget.data.mode==='until')return days<=0?'D-'+Math.abs(days):'D+'+days;return days>=0?'D+'+(days+1):'D-'+Math.abs(days);}
  function renderContent(widget,content){
    var data=widget.data||{};
    if(widget.type==='image'){
      content.className='pair-widget-content pair-widget-image shape-'+(data.shape||'rounded');
      if(data.src){var img=document.createElement('img');img.src=data.src;img.alt=data.alt||'';content.appendChild(img);}else addEmpty(content,'이미지 프레임');
      content.addEventListener('click',function(){navigate(data.link);});
    }else if(widget.type==='sticker'){
      content.className='pair-widget-content pair-widget-sticker';
      if(data.src){var sticker=document.createElement('img');sticker.src=data.src;sticker.alt=data.alt||'';content.appendChild(sticker);}else addEmpty(content,'STICKER');
      content.addEventListener('click',function(){navigate(data.link);});
    }else if(widget.type==='text'){
      content.className='pair-widget-content pair-widget-text';content.textContent=data.text||'텍스트';content.addEventListener('click',function(){navigate(data.link);});
    }else if(widget.type==='dday'){
      content.className='pair-widget-content pair-widget-dday';var label=document.createElement('small');label.textContent=data.title||'D-DAY';var value=document.createElement('strong');value.textContent=ddayText(widget);content.append(label,value);
    }else if(widget.type==='bgm'){
      content.className='pair-widget-content pair-widget-bgm';var title=document.createElement('strong');title.textContent=data.title||'BGM';var audio=document.createElement('audio');audio.controls=true;audio.preload='metadata';if(data.src)audio.src=data.src;content.append(title,audio);
    }else if(widget.type==='category'){
      content.className='pair-widget-content pair-widget-category';var heading=document.createElement('h3');heading.textContent=data.title||'MENU';content.appendChild(heading);(data.items||[]).forEach(function(item){var button=document.createElement('button');button.type='button';button.textContent=item.label||'메뉴';button.disabled=!item.url;button.addEventListener('click',function(e){e.stopPropagation();navigate(item.url);});content.appendChild(button);});
    }
  }
  function addEmpty(content,label){var empty=document.createElement('div');empty.className='pair-empty';empty.textContent=label;content.appendChild(empty);}
  function render(updatePanel){
    if(updatePanel===undefined)updatePanel=true;applyBackground();canvas.innerHTML='';
    state.widgets.forEach(function(widget){var el=document.createElement('div');el.className='pair-widget'+(state.selectedId===widget.id&&state.editing?' is-selected':'');el.dataset.id=widget.id;el.dataset.type=widget.type;positionElement(el,widget);var content=document.createElement('div');renderContent(widget,content);el.appendChild(content);if(state.editing){var handle=document.createElement('span');handle.className='pair-resize-handle';handle.setAttribute('aria-hidden','true');el.appendChild(handle);}canvas.appendChild(el);});
    if(updatePanel)renderProperties();
  }
  function positionElement(el,widget){el.style.left=widget.x+'%';el.style.top=widget.y+'%';el.style.width=widget.w+'%';el.style.height=widget.h+'%';el.style.zIndex=widget.z;el.style.transform='rotate('+widget.rotation+'deg)';}
  function field(label,type,value,key,options){var wrap=document.createElement('label');wrap.className='pair-editor-field';var caption=document.createElement('span');caption.textContent=label;wrap.appendChild(caption);var input;if(type==='textarea')input=document.createElement('textarea');else if(type==='select'){input=document.createElement('select');(options||[]).forEach(function(option){var node=document.createElement('option');node.value=option.value;node.textContent=option.label;input.appendChild(node);});}else{input=document.createElement('input');input.type=type;}input.value=value==null?'':value;input.dataset.key=key;wrap.appendChild(input);return wrap;}
  function renderProperties(){
    if(!config.admin)return;var panel=document.getElementById('pair-editor-panel');if(!panel)return;panel.innerHTML='';
    if(!state.editing){panel.classList.remove('is-open');return;}var widget=byId(state.selectedId);if(!widget){panel.classList.remove('is-open');return;}panel.classList.add('is-open');
    var title=document.createElement('h2');title.textContent='위젯 설정 · '+widget.type;panel.appendChild(title);var data=widget.data||{};
    if(widget.type==='image'){panel.appendChild(field('프레임 모양','select',data.shape||'rounded','shape',[{value:'square',label:'사각형'},{value:'rounded',label:'둥근 사각형'},{value:'circle',label:'원형'}]));panel.appendChild(field('대체 텍스트','text',data.alt||'','alt'));panel.appendChild(field('연결 주소','text',data.link||'','link'));}
    else if(widget.type==='sticker'){panel.appendChild(field('대체 텍스트','text',data.alt||'','alt'));panel.appendChild(field('연결 주소','text',data.link||'','link'));}
    else if(widget.type==='text'){panel.appendChild(field('내용','textarea',data.text||'','text'));panel.appendChild(field('연결 주소','text',data.link||'','link'));}
    else if(widget.type==='dday'){panel.appendChild(field('제목','text',data.title||'','title'));panel.appendChild(field('기준일','date',data.date||'','date'));panel.appendChild(field('계산 방식','select',data.mode||'since','mode',[{value:'since',label:'기준일부터'},{value:'until',label:'기준일까지'}]));}
    else if(widget.type==='bgm'){panel.appendChild(field('곡명','text',data.title||'','title'));panel.appendChild(field('음원 주소','text',data.src||'','src'));}
    else if(widget.type==='category'){panel.appendChild(field('제목','text',data.title||'','title'));panel.appendChild(field('메뉴 항목 · 이름|주소','textarea',(data.items||[]).map(function(i){return i.label+'|'+(i.url||'');}).join('\n'),'items'));}
    panel.appendChild(field('회전','range',widget.rotation,'rotation'));var range=panel.querySelector('[data-key="rotation"]');if(range){range.min=-30;range.max=30;range.step=1;}
    var actions=document.createElement('div');actions.className='pair-editor-actions';
    if(widget.type==='image'||widget.type==='sticker'){var upload=document.createElement('button');upload.type='button';upload.textContent='이미지 업로드';upload.addEventListener('click',function(){activeUploadWidgetId=widget.id;document.getElementById('pair-asset-upload').click();});actions.appendChild(upload);}
    var front=document.createElement('button');front.type='button';front.textContent='맨 앞으로';front.addEventListener('click',function(){var z=state.widgets.map(function(i){return i.z||1;});widget.z=(z.length?Math.max.apply(null,z):0)+1;render();});actions.appendChild(front);
    var remove=document.createElement('button');remove.type='button';remove.className='pair-danger';remove.textContent='삭제';remove.addEventListener('click',function(){state.widgets=state.widgets.filter(function(i){return i.id!==widget.id;});state.selectedId=null;render();});actions.appendChild(remove);panel.appendChild(actions);
    panel.querySelectorAll('[data-key]').forEach(function(input){input.addEventListener('input',function(){var key=input.dataset.key;if(key==='rotation')widget.rotation=Number(input.value);else if(key==='items')widget.data.items=input.value.split(/\r?\n/).map(function(line){var at=line.indexOf('|');return{label:(at>=0?line.slice(0,at):line).trim(),url:(at>=0?line.slice(at+1):'').trim()};}).filter(function(i){return i.label;});else widget.data[key]=input.value;render(false);});});
  }
  function addWidget(type){var defaults={image:{src:'',shape:'rounded',alt:'',link:''},sticker:{src:'',alt:'',link:''},text:{text:'새 텍스트',link:''},dday:{title:'D-DAY',date:new Date().toISOString().slice(0,10),mode:'since'},bgm:{title:'BGM',src:''},category:{title:'MENU',items:[{label:'새 카테고리',url:''}]}};var z=state.widgets.reduce(function(max,w){return Math.max(max,w.z||1);},0);var widget={id:newId(type),type:type,x:35,y:30,w:type==='sticker'?14:24,h:type==='text'?14:24,rotation:0,z:z+1,data:defaults[type]};state.widgets.push(widget);state.selectedId=widget.id;render();}
  function showStatus(message){var status=document.getElementById('pair-editor-status');if(!status)return;status.textContent=message;status.style.display='block';clearTimeout(statusTimer);statusTimer=setTimeout(function(){status.style.display='none';},2800);}
  async function uploadImage(file,kind){var form=new FormData();form.append('action','upload');form.append('kind',kind);form.append('token',config.token||'');form.append('image',file);var response=await fetch(config.apiUrl,{method:'POST',body:form,credentials:'same-origin'});var result=await response.json();if(!response.ok||result.error)throw new Error(result.error||'업로드에 실패했습니다.');return result.url;}
  async function save(){var form=new FormData();form.append('action','save');form.append('token',config.token||'');form.append('layout',JSON.stringify(state.widgets));form.append('background_color',state.backgroundColor);form.append('background_image',state.backgroundImage);var response=await fetch(config.apiUrl,{method:'POST',body:form,credentials:'same-origin'});var result=await response.json();if(!response.ok||result.error)throw new Error(result.error||'저장에 실패했습니다.');state.widgets=result.layout;render();showStatus('저장했습니다.');}
  function openBackground(){var panel=document.getElementById('pair-editor-panel');state.selectedId=null;canvas.querySelectorAll('.is-selected').forEach(function(el){el.classList.remove('is-selected');});panel.innerHTML='';panel.classList.add('is-open');var title=document.createElement('h2');title.textContent='배경 설정';panel.appendChild(title);var color=field('배경색','color',state.backgroundColor,'backgroundColor');panel.appendChild(color);color.querySelector('input').addEventListener('input',function(e){state.backgroundColor=e.target.value.toUpperCase();applyBackground();});var note=document.createElement('p');note.className='pair-editor-note';note.textContent='배경 이미지는 비율을 유지한 채 화면을 항상 채우며, 넘치는 부분은 자동으로 잘립니다.';panel.appendChild(note);var actions=document.createElement('div');actions.className='pair-editor-actions';var upload=document.createElement('button');upload.type='button';upload.textContent='배경 이미지 업로드';upload.addEventListener('click',function(){document.getElementById('pair-background-upload').click();});var clear=document.createElement('button');clear.type='button';clear.textContent='배경 이미지 제거';clear.addEventListener('click',function(){state.backgroundImage='';applyBackground();});actions.append(upload,clear);panel.appendChild(actions);}
  canvas.addEventListener('pointerdown',function(event){if(!state.editing)return;var el=event.target.closest('.pair-widget');if(!el){state.selectedId=null;render();return;}event.preventDefault();state.selectedId=el.dataset.id;canvas.querySelectorAll('.pair-widget').forEach(function(node){node.classList.toggle('is-selected',node===el);});renderProperties();var widget=byId(state.selectedId);if(!widget)return;var rect=canvas.getBoundingClientRect();interaction={id:widget.id,mode:event.target.classList.contains('pair-resize-handle')?'resize':'move',startX:event.clientX,startY:event.clientY,x:widget.x,y:widget.y,w:widget.w,h:widget.h,rect:rect};el.setPointerCapture(event.pointerId);});
  canvas.addEventListener('pointermove',function(event){if(!interaction)return;var widget=byId(interaction.id);if(!widget)return;var dx=(event.clientX-interaction.startX)/interaction.rect.width*100,dy=(event.clientY-interaction.startY)/interaction.rect.height*100;if(interaction.mode==='move'){widget.x=clamp(interaction.x+dx,0,100-widget.w);widget.y=clamp(interaction.y+dy,0,100-widget.h);}else{widget.w=clamp(interaction.w+dx,4,100-widget.x);widget.h=clamp(interaction.h+dy,4,100-widget.y);}var el=canvas.querySelector('[data-id="'+widget.id+'"]');if(el)positionElement(el,widget);});
  canvas.addEventListener('pointerup',function(){interaction=null;});canvas.addEventListener('pointercancel',function(){interaction=null;});
  if(config.admin){
    document.getElementById('pair-editor-toggle').addEventListener('click',function(){state.editing=!state.editing;shell.classList.toggle('is-editing',state.editing);this.textContent=state.editing?'꾸미기 종료':'꾸미기';if(!state.editing)state.selectedId=null;render();});
    document.querySelectorAll('[data-add]').forEach(function(button){button.addEventListener('click',function(){addWidget(button.dataset.add);});});
    document.getElementById('pair-background-open').addEventListener('click',openBackground);
    document.getElementById('pair-save').addEventListener('click',function(){save().catch(function(error){showStatus(error.message);});});
    document.getElementById('pair-asset-upload').addEventListener('change',function(e){var file=e.target.files[0],widget=byId(activeUploadWidgetId);if(!file||!widget)return;uploadImage(file,'asset').then(function(url){widget.data.src=url;render();showStatus('이미지를 추가했습니다.');}).catch(function(error){showStatus(error.message);});e.target.value='';});
    document.getElementById('pair-background-upload').addEventListener('change',function(e){var file=e.target.files[0];if(!file)return;uploadImage(file,'background').then(function(url){state.backgroundImage=url;applyBackground();showStatus('배경 이미지를 적용했습니다.');}).catch(function(error){showStatus(error.message);});e.target.value='';});
  }
  render();
})();
