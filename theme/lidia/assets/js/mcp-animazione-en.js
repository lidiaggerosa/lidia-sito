/**
 * Animazione «Lidia in Claude», versione inglese, per /en/product/mcp-server/ e il box della
 * home EN (23/10/2026). Sorgente: «Claude MPC Inglese», logica invariata. Rispetto alla versione
 * italiana cambiano testi e tempi di battitura. Caricato solo dove c'è `lidia-mcp-visivo-en`.
 */
(function(){
  document.querySelectorAll('.ldc:not([data-ldc])').forEach(function(root){
    root.setAttribute('data-ldc','1');
    var $=function(s){return root.querySelector(s)}, $$=function(s){return Array.prototype.slice.call(root.querySelectorAll(s))};
    var stage=$('.ldc-stage'), win=$('.ldc-win'), scale=1;
    function fit(){scale=root.clientWidth/560;stage.style.transform='scale('+scale+')';}
    fit(); if(window.ResizeObserver) new ResizeObserver(fit).observe(root); else window.addEventListener('resize',fit);
    var E={gr:$('.ldc-gr'),comp:$('.ldc-comp'),catt:$('.ldc-catt'),a1:$('.ldc-a1'),a2:$('.ldc-a2'),pr1:$('.ldc-a1 .ldc-prog'),pr2:$('.ldc-a2 .ldc-prog'),inp:$('.ldc-input'),ph:$('.ldc-ph'),ut:$('.ldc-ut'),caret:$('.ldc-caret'),plus:$('.ldc-plus'),send:$('.ldc-send'),wave:$('.ldc-wave'),
      menu:$('.ldc-menu'),hl:$('.ldc-hl'),up:$('.ldc-up'),q:$('.ldc-q'),tool:$('.ldc-tool'),spin:$('.ldc-spin'),ok:$('.ldc-ok'),ans:$$('.ldc-ans>i'),ring:$('.ldc-ring'),cur:$('.ldc-cur')};
    var rows=$$('.ldc-menu .ldc-row');
    var c01=function(x){return x<0?0:x>1?1:x}, seg=function(t,a,b){return c01((t-a)/(b-a))};
    var eo=function(x){return 1-Math.pow(1-x,3)}, eio=function(x){return x<.5?4*x*x*x:1-Math.pow(-2*x+2,3)/2}, lerp=function(a,b,k){return a+(b-a)*k};
    function ctr(el){var r=el.getBoundingClientRect(),w=win.getBoundingClientRect();return [(r.left+r.width/2-w.left)/scale,(r.top+r.height/2-w.top)/scale];}
    var C=3.6, TE=13.104, S=14.054, DUR=S+7.2, TXT="Ask Lidia to answer the following legal question: does the mandatory mediation under Article 5 of Legislative Decree 28/2010 apply to disputes arising from contracts for works? What does Court of Cassation case law say about the list of matters?";
    var E2={sub:$('.ldc-sub'),conn:$('.ldc-conn'),lid:$('.ldc-lid'),ltog:$('.ldc-ltog'),chip:$('.ldc-chip')};
    function render(t){
      var clr=eio(seg(t,S,S+.25)), mv=eio(seg(t,S,S+.6));
      var attH=44*eio(seg(t,2.3+C,2.6+C))*(1-clr);
      E.catt.style.height=attH+'px'; E.catt.style.paddingBottom='8px';
      // typing (real text)
      var n=Math.max(0,Math.min(TXT.length,Math.floor((t-4.4-C)*48)));
      E.ut.firstChild.textContent=t<S?TXT.slice(0,n):''; E.ut.style.opacity=1-clr;
      E.ph.style.display=(t>4.38+C&&t<S+.2)?'none':'block'; E.ph.textContent=t>=S?'Reply\u2026':'How can I help you today?';
      E.ph.style.opacity=t<S?1:eo(seg(t,S+.25,S+.55));
      var typing=t>4.38+C&&t<TE+.1;
      E.caret.style.opacity=(t>4.15+C&&t<S&&(typing||Math.floor(t/.5)%2===0))?1:0;
      var hc=E.comp.offsetHeight, homeTop=190, chatTop=440-16-hc;
      E.comp.style.left=lerp(40,16,mv)+'px'; E.comp.style.right=lerp(40,16,mv)+'px'; E.comp.style.top=lerp(homeTop,chatTop,mv)+'px';
      E.gr.style.opacity=1-eo(seg(t,S-.05,S+.25)); E.gr.style.transform='translateY('+(-20*eo(seg(t,S-.05,S+.35)))+'px)';
      // menu: open once for connettori (if C) and once for upload
      var m1=C?eo(seg(t,1.35,1.5))*(1-eo(seg(t,3.5,3.7))):0, m2=eo(seg(t,1.35+C,1.5+C))*(1-eo(seg(t,2.25+C,2.4+C)));
      var mo=Math.max(m1,m2); E.menu.style.opacity=mo; E.menu.style.transform='scale('+lerp(.96,1,mo)+')';
      E.menu.style.top=(190-8-E.menu.offsetHeight)+'px';
      var pon=(C&&t>1.3&&t<3.6)||(t>1.3+C&&t<2.35+C); E.plus.style.background=pon?'var(--ldc-hover)':'';
      if(E2.sub){
        var sv=t>=2.2?eo(seg(t,2.2,2.35)):0; E2.sub.style.opacity=sv*(1-eo(seg(t,3.5,3.7))); E2.sub.style.transform='scale('+lerp(.97,1,sv)+')';
        E2.sub.style.top=(190-8-E2.sub.offsetHeight)+'px';
        E2.conn.classList.toggle('on',t>=2.15&&t<3.7);
        var hv=eo(seg(t,2.75,2.95)); E2.lid.style.background='rgba(48,58,153,'+(hv*.09)+')'; E2.lid.style.boxShadow=t>3.05&&t<3.7?'inset 0 0 0 1.5px var(--ldc-blue)':'none';
        var o=eio(seg(t,3.05,3.2)); E2.ltog.firstChild.style.transform='translateX('+(o*14)+'px)'; E2.ltog.style.background=o>.5?'var(--ldc-blue)':'';
        var ch=eo(seg(t,3.6,3.9)); E2.chip.style.opacity=ch; E2.chip.style.transform='scale('+lerp(.9,1,ch)+')';
      }
      // uploads
      var p1=eo(seg(t,2.35+C,2.6+C)),p2=eo(seg(t,2.55+C,2.8+C));
      E.a1.style.opacity=p1*(1-clr); E.a1.style.transform='scale('+lerp(.92,1,p1)+')'; E.a2.style.opacity=p2*(1-clr); E.a2.style.transform='scale('+lerp(.92,1,p2)+')';
      [[E.pr1,2.4+C,3.2+C],[E.pr2,2.6+C,3.4+C]].forEach(function(q){var u=eio(seg(t,q[1],q[2]));q[0].firstChild.style.transform='scaleX('+u+')';q[0].style.opacity=1-eo(seg(t,q[2]+.05,q[2]+.3));});
      E.comp.style.borderColor=t>4.1+C&&t<S+.05?'rgba(0,0,0,.28)':'';
      var sb=eo(seg(t,4.45+C,4.7+C))*(1-eo(seg(t,S+.05,S+.25))); E.send.style.opacity=sb; E.send.style.transform='scale('+(lerp(.7,1,sb)*(1-.12*Math.sin(Math.PI*seg(t,S-.13,S+.01))))+')'; E.wave.style.opacity=1-sb;
      // chat
      var r=eio(seg(t,S+.15,S+.7)); E.q.style.opacity=c01(r*1.6); E.q.style.transform='translateY('+lerp(220,0,r)+'px)';
      var tl=eo(seg(t,S+.95,S+1.25)); E.tool.style.opacity=tl; E.tool.style.transform='translateY('+lerp(10,0,tl)+'px)';
      var done=t>S+1.95; E.spin.style.opacity=done||tl===0?0:1; E.spin.style.transform='rotate('+(t*400)+'deg)';
      var k=eo(seg(t,S+1.95,S+2.15)); E.ok.style.opacity=k; E.ok.style.transform='scale('+lerp(.6,1,k)+')';
      E.ans.forEach(function(a,i){var p=eio(seg(t,S+2.05+i*.24,S+2.4+i*.24));a.style.transform='scaleX('+p+')';});
      // cursor
      var P0=[480,400],Pp=ctr(E.plus),Pf=ctr(E.up),Pi=ctr(E.inp),Ps=ctr(E.send);Pf[0]-=20;Pi[0]+=40;
      var keys=[];
      if(C){var Pc=ctr(E2.conn),Pt=ctr(E2.ltog);Pc[0]+=40;
        keys.push([.5,1.2,P0,Pp],[1.6,2.1,Pp,Pc],[2.45,3.0,Pc,Pt],[3.9,4.8,Pt,Pp]);
      } else keys.push([.5,1.2,P0,Pp]);
      var Po=[505,416];
      keys.push([1.6+C,2.1+C,Pp,Pf],[3.4+C,4.0+C,Pf,Pi],[4.3+C,4.95+C,Pi,Po],[TE+.2,TE+.75,Po,Ps]);
      var x=P0[0],y=P0[1];
      keys.forEach(function(kk){if(t>=kk[0]){var e=eio(seg(t,kk[0],kk[1]));x=lerp(kk[2][0],kk[3][0],e);y=lerp(kk[2][1],kk[3][1],e)-Math.sin(e*Math.PI)*14;}});
      var clicks=[[1.25,Pp]]; if(C) clicks.push([2.12,Pc],[3.02,Pt],[4.85,Pp]);
      clicks.push([2.15+C,Pf],[4.05+C,Pi],[S-.12,Ps]);
      var press=0,rr=-1,rp=[0,0];
      clicks.forEach(function(c){press+=Math.sin(Math.PI*seg(t,c[0],c[0]+.12)); if(t>c[0]+.03&&t<c[0]+.35){rr=seg(t,c[0]+.03,c[0]+.35);rp=c[1];}});
      E.cur.style.transform='translate('+x+'px,'+y+'px) scale('+(1-.14*press)+')';
      E.cur.style.opacity=eo(seg(t,.3,.55))*(1-eo(seg(t,S+.05,S+.35)));
      E.ring.style.opacity=rr>0&&rr<1?1-rr:0; E.ring.style.transform='translate('+rp[0]+'px,'+rp[1]+'px) scale('+lerp(.4,1.3,eo(c01(rr)))+')';
      var best=1e9,bi=0; rows.forEach(function(rw,i){var c=ctr(rw);var d=Math.abs(c[1]-y);if(d<best){best=d;bi=i;}});
      var hlOn=(t>1.7+C&&t<2.25+C)||(C&&t>1.7&&t<2.15);
      E.hl.style.opacity=hlOn&&best<16?1:0; E.hl.style.top=rows[bi].offsetTop+'px';
      win.style.opacity=1-eo(seg(t,DUR-.5,DUR));
    }
    var reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if(reduce){render(DUR-1.5);return;}
    var visible=true,start=null,last=0;
    if(window.IntersectionObserver) new IntersectionObserver(function(es){visible=es[0].isIntersecting;}).observe(root);
    function tick(now){if(start===null)start=now; if(!visible){start=now-last*1000;} else {last=((now-start)/1000)%DUR; render(last);} requestAnimationFrame(tick);}
    render(0); requestAnimationFrame(tick);
  });
})();
