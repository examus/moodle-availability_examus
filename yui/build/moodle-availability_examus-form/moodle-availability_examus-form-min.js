YUI.add("moodle-availability_examus-form",function(F,e){M.availability_examus=M.availability_examus||{},M.availability_examus.form=F.Object(M.core_availability.plugin),M.availability_examus.form.rules=null,M.availability_examus.form.initInner=function(e,i,n,a){this.rules=e,this.groups=i,this.warnings=n,this.scoring=a},M.availability_examus.form.instId=0,M.availability_examus.form.getNode=function(e){var i,n,a,t,s,o,l,r,u,c,d,m,p,g,f,b,v,h,_,y,x,k,w,C,q,I,N,T,R,A,O,D,E,V,j,H,L,P,S;function U(e,i){return i=i||"availability_examus",M.util.get_string(e,i)}function Y(e){var i=U("showmore","core_form"),n=U("showless","core_form");return'<a href="#" class="examus-moreless" data-more="'+i+'" data-less="'+n+'">'+i+'</a><div class="hidden col-md-12">'+e+"</div>"}function $(e,i,n,a){var t=a?10:5,a=a?10:7;return'<span class="availability-group form-group mb-2"><div class="col-md-'+t+' col-form-label d-flex pb-0 pr-md-0">  <label for="'+e+'">'+i+'</label></div><div class="col-md-'+a+' form-inline align-items-start felement">'+n+"</div></span>"}function z(e){setTimeout(e,0)}function B(e){1==e?(R.addClass("btn-primary"),R.removeClass("btn-secondary"),A.addClass("btn-secondary"),A.removeClass("btn-primary"),O.removeClass("hidden"),D.addClass("hidden")):(A.addClass("btn-primary"),A.removeClass("btn-secondary"),R.addClass("btn-secondary"),R.removeClass("btn-primary"),O.addClass("hidden"),D.removeClass("hidden"))}for(g in M.availability_examus.form.instId+=1,s=(t="examus"+M.availability_examus.form.instId)+"_mode",o=t+"_schedulingRequired",l=t+"_autoRescheduling",r=t+"_isTrial",T=t+"_identification",u=t+"_customRules",c=t+"_noProtection",d=t+"_auxCamera",m=t+"userAgreement",i=$(N=t+"_duration",U("duration"),'<input type="text" name="duration" id="'+N+'" class="form-control">'),i+=$(s,U("mode"),'<select name="mode" id="'+s+'" class="custom-select">  <option value="normal">'+U("normal_mode")+'</option>  <option value="identification">'+U("identification_mode")+'</option>  <option value="olympics">'+U("olympics_mode")+'</option>  <option value="auto">'+U("auto_mode")+"</option></select>"),i+=$(T,U("identification"),'<select name="identification" id="'+T+'" class="custom-select">  <option value="passport">'+U("passport_identification")+'</option>  <option value="face">'+U("face_identification")+'</option>  <option value="face_and_passport">'+U("face_passport_identification")+'</option>  <option value="skip">'+U("skip_identification")+"</option></select>"),i+=$(o,U("scheduling_required"),'<input type="checkbox" name="scheduling_required" id="'+o+'" value="1">&nbsp;<label for="'+o+'">'+U("enable")+"</label> "),i+=$(l,U("auto_rescheduling"),'<input type="checkbox" name="auto_rescheduling" id="'+l+'" value="1">&nbsp;<label for="'+l+'">'+U("enable")+"</label> "),i+=$(r,U("is_trial"),'<input type="checkbox" name="istrial" id="'+r+'" value="1">&nbsp;<label for="'+r+'">'+U("enable")+"</label> "),i+=$(c,U("noprotection"),'<input type="checkbox" name="noprotection" id="'+c+'" value="1">&nbsp;<label for="'+c+'">'+U("enable")+"</label> "),i+=$(r,U("auxiliary_camera"),'<input type="checkbox" name="auxiliarycamera" id="'+d+'" value="1">&nbsp;<label for="'+d+'">'+U("enable")+"</label> "),i+=$(m,U("user_agreement_url"),'<input name="useragreementurl" id="'+m+'" class="form-control" value="" />'),i+=$(u,U("custom_rules"),'<textarea name="customrules" id="'+u+'" style="width: 100%" class="form-control"></textarea>'),p="",this.rules)p+='<br><input type="checkbox" name="'+g+'" id="'+(f=t+"_"+g)+'" value="'+g+'" >&nbsp;',p+='<label for="'+f+'" style="white-space: break-spaces">'+U(g)+"</label>";if(i+=$(null,U("rules"),'<div class="rules" style="white-space:nowrap">'+p+"</div>"),this.groups){for(v in b="",this.groups)h=this.groups[v].name,_=e.groups instanceof Array?e.groups:[],b+="<br><label><input value="+(t=parseInt(this.groups[v].id))+' type="checkbox" name=groups[] '+(-1<(_=_.map(function(e){return parseInt(e)})).indexOf(t)?"checked":"")+">&nbsp;"+h+"</label>";i+=$(null,U("select_groups"),'<div class="groups"'+b+"</div>")}for(x in y="",this.warnings)y+='<input type="checkbox" name="'+x+'" id="'+(k=t+"_"+x)+'" value="'+x+'" >&nbsp;',y+='<label for="'+k+'" style="white-space: break-spaces">'+U(x)+"</label><br>";for(C in w="",this.scoring)I='<input type="number" class="examus-scoring-input" value=""name="'+C+'"id="scoring_'+(q=t+"_"+C)+'"min="'+this.scoring[C].min+'" max="'+this.scoring[C].max+'">',w+=$(q,U("scoring_"+C),I);if(N="",N+=$(null,U("visible_warnings"),'<div class="warnings" style="white-space: nowrap" >'+Y(y)+"</div>",!0),N+=$(null,U("visible_warnings"),Y(w),!0),(n=F.Node.create('<span class="availibility_examus-tabs" style="position:relative"></span>')).setHTML("<label><strong>"+U("title")+"</strong></label><br><br>"),T=F.Node.create('<div style="position:absolute; top: 0; right: 0;" class="availibility_examus-tab-btns"></div>').appendTo(n),R=F.Node.create('<a href="#" class="btn btn-primary">1</a>').appendTo(T),A=F.Node.create('<a href="#" class="btn btn-secondary">2</a>').appendTo(T),O=F.Node.create('<div class="tab_content">'+i+"</div>").appendTo(n),D=F.Node.create('<div class="tab_content hidden">'+N+"</div>").appendTo(n),e.creating&&(e.mode="normal",e.scheduling_required=!0),e.duration!==undefined&&n.one("input[name=duration]").set("value",e.duration),e.mode!==undefined&&n.one("select[name=mode] option[value="+e.mode+"]").set("selected","selected"),e.identification!==undefined&&n.one("select[name=identification] option[value="+e.identification+"]").set("selected","selected"),e.auto_rescheduling!==undefined&&(a=e.auto_rescheduling?"checked":null,n.one("#"+l).set("checked",a)),e.noprotection!==undefined&&(a=e.noprotection?"checked":null,n.one("#"+c).set("checked",a)),e.istrial!==undefined&&(a=e.istrial?"checked":null,n.one("#"+r).set("checked",a)),e.auxiliarycamera!==undefined&&(a=e.auxiliarycamera?"checked":null,n.one("#"+d).set("checked",a)),e.scheduling_required!==undefined&&(
a=e.scheduling_required?"checked":null,n.one("#"+o).set("checked",a)),e.rules===undefined&&(e.rules=this.rules),e.warnings===undefined)e.warnings=this.warnings;else for(x in E=e.warnings,e.warnings=this.warnings,E)e.warnings[x]=E[x];for(V in e.rules)e.rules[V]&&(j=n.one(".rules input[name="+V+"]"))&&j.set("checked","checked");for(H in e.warnings)e.warnings[H]&&(L=n.one(".warnings input[name="+H+"]"))&&L.set("checked","checked");for(P in e.scoring=e.scoring||{},e.scoring)e.scoring[P]&&(S=n.one(".examus-scoring-input[name="+P+"]"))&&S.set("value",e.scoring[P]);return e.customrules!==undefined&&n.one("#"+u).set("value",e.customrules),e.useragreementurl!==undefined&&n.one("#"+m).set("value",e.useragreementurl),n.delegate("valuechange",function(){z(function(){M.core_availability.form.update()})},"input,textarea,select"),n.delegate("click",function(){z(function(){M.core_availability.form.update()})},"input[type=checkbox]"),n.delegate("valuechange",function(){var e,i;e=["normal","identification"],i=n.one("select[name=mode]").get("value").trim(),e=0<=e.indexOf(i),n.one("#"+o).set("checked",e)},"#"+s),R.on("click",function(e){e.preventDefault(),B(1)}),A.on("click",function(e){e.preventDefault(),B(2)}),n.delegate("click",function(e){var i;e.preventDefault(),i=e.target,e=i.next(),e.hasClass("hidden")?(e.removeClass("hidden"),i.setContent(i.getAttribute("data-less"))):(e.addClass("hidden"),i.setContent(i.getAttribute("data-more")))},".examus-moreless"),n},M.availability_examus.form.fillValue=function(n,e){var i,a,t;n.duration=e.one("input[name=duration]").get("value").trim(),n.mode=e.one("select[name=mode]").get("value").trim(),n.identification=e.one("select[name=identification]").get("value").trim(),n.auto_rescheduling=e.one("input[name=auto_rescheduling]").get("checked"),n.scheduling_required=e.one("input[name=scheduling_required]").get("checked"),n.istrial=e.one("input[name=istrial]").get("checked"),n.customrules=e.one("textarea[name=customrules]").get("value").trim(),n.noprotection=e.one("input[name=noprotection]").get("checked"),n.useragreementurl=e.one("input[name=useragreementurl]").get("value").trim(),n.auxiliarycamera=e.one("input[name=auxiliarycamera]").get("checked"),n.rules={},i=e.all(".rules input"),F.each(i,function(e){t=e.get("value"),!0===e.get("checked")?n.rules[t]=!0:n.rules[t]=!1}),n.warnings={},a=e.all(".warnings input"),F.each(a,function(e){t=e.get("value"),!0===e.get("checked")?n.warnings[t]=!0:n.warnings[t]=!1}),n.scoring={},a=e.all(".examus-scoring-input"),F.each(a,function(e){t=e.get("name");e=e.get("value").trim();0<e.length?n.scoring[t]=parseInt(e):n.scoring[t]=null}),n.groups=[],i=e.all(".groups input"),F.each(i,function(e){var i=e.get("value");!0===e.get("checked")&&n.groups.push(i)}),console.log(n)},M.availability_examus.form.fillErrors=function(e,i){var n={};this.fillValue(n,i),n.duration!==undefined&&new RegExp("^\\d+$").test(n.duration)&&n.duration%30==0||e.push("availability_examus:error_setduration")}},"@VERSION@",{requires:["base","node","event","moodle-core_availability-form"]});