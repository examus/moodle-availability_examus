YUI.add("moodle-availability_examus-form",function(P,e){M.availability_examus=M.availability_examus||{},M.availability_examus.form=P.Object(M.core_availability.plugin),M.availability_examus.form.rules=null,M.availability_examus.form.initInner=function(e,i,a){this.rules=e,this.groups=i,this.warnings=a},M.availability_examus.form.instId=0,M.availability_examus.form.getNode=function(e){var i,a,n,t,l,o,s,r,u,c,d,p,m,f,b,g,v,h,_,y,x,k,w,C,q,I,N,T,R,O,E,V,A;function D(e){return M.util.get_string(e,"availability_examus")}function j(e,i,a){return'<span class="availability-group form-group mb-2"><div class="col-md-5 col-form-label d-flex pb-0 pr-md-0">  <label for="'+e+'">'+i+'</label></div><div class="col-md-7 form-inline align-items-start felement">'+a+"</div></span>"}function H(e){setTimeout(e,0)}function L(e){1==e?(q.addClass("btn-primary"),q.removeClass("btn-secondary"),I.addClass("btn-secondary"),I.removeClass("btn-primary"),N.removeClass("hidden"),T.addClass("hidden")):(I.addClass("btn-primary"),I.removeClass("btn-secondary"),q.addClass("btn-secondary"),q.removeClass("btn-primary"),N.addClass("hidden"),T.removeClass("hidden"))}for(f in M.availability_examus.form.instId+=1,l=(t="examus"+M.availability_examus.form.instId)+"_mode",o=t+"_schedulingRequired",s=t+"_autoRescheduling",r=t+"_isTrial",C=t+"_identification",u=t+"_customRules",c=t+"_noProtection",d=t+"_auxCamera",p=t+"userAgreement",i=j(w=t+"_duration",D("duration"),'<input type="text" name="duration" id="'+w+'" class="form-control">'),i+=j(l,D("mode"),'<select name="mode" id="'+l+'" class="custom-select">  <option value="normal">'+D("normal_mode")+'</option>  <option value="identification">'+D("identification_mode")+'</option>  <option value="olympics">'+D("olympics_mode")+'</option>  <option value="auto">'+D("auto_mode")+"</option></select>"),i+=j(C,D("identification"),'<select name="identification" id="'+C+'" class="custom-select">  <option value="passport">'+D("passport_identification")+'</option>  <option value="face">'+D("face_identification")+'</option>  <option value="face_and_passport">'+D("face_passport_identification")+'</option>  <option value="skip">'+D("skip_identification")+"</option></select>"),i+=j(o,D("scheduling_required"),'<input type="checkbox" name="scheduling_required" id="'+o+'" value="1">&nbsp;<label for="'+o+'">'+D("enable")+"</label> "),i+=j(s,D("auto_rescheduling"),'<input type="checkbox" name="auto_rescheduling" id="'+s+'" value="1">&nbsp;<label for="'+s+'">'+D("enable")+"</label> "),i+=j(r,D("is_trial"),'<input type="checkbox" name="istrial" id="'+r+'" value="1">&nbsp;<label for="'+r+'">'+D("enable")+"</label> "),i+=j(c,D("noprotection"),'<input type="checkbox" name="noprotection" id="'+c+'" value="1">&nbsp;<label for="'+c+'">'+D("enable")+"</label> "),i+=j(r,D("auxiliary_camera"),'<input type="checkbox" name="auxiliarycamera" id="'+d+'" value="1">&nbsp;<label for="'+d+'">'+D("enable")+"</label> "),i+=j(p,D("user_agreement_url"),'<input name="useragreementurl" id="'+p+'" class="form-control" value="" />'),i+=j(u,D("custom_rules"),'<textarea name="customrules" id="'+u+'" style="width: 100%" class="form-control"></textarea>'),m="",this.rules)m+='<br><input type="checkbox" name="'+f+'" id="'+(b=t+"_"+f)+'" value="'+f+'" >&nbsp;',m+='<label for="'+b+'" style="white-space: break-spaces">'+D(f)+"</label>";if(i+=j(null,D("rules"),'<div class="rules" style="white-space:nowrap">'+m+"</div>"),this.groups){for(v in g="",this.groups)h=this.groups[v].name,_=e.groups instanceof Array?e.groups:[],g+="<br><label><input value="+(t=parseInt(this.groups[v].id))+' type="checkbox" name=groups[] '+(-1<(_=_.map(function(e){return parseInt(e)})).indexOf(t)?"checked":"")+">&nbsp;"+h+"</label>";i+=j(null,D("select_groups"),'<div class="groups"'+g+"</div>")}for(x in y="",this.warnings)y+='<br><input type="checkbox" name="'+x+'" id="'+(k=t+"_"+x)+'" value="'+x+'" >&nbsp;',y+='<label for="'+k+'" style="white-space: break-spaces">'+D(x)+"</label>";if(w=j(null,D("visible_warnings"),'<div class="warnings" style="white-space: nowrap" '+y+"</div>"),(a=P.Node.create('<span class="availibility_examus-tabs" style="position:relative"></span>')).setHTML("<label><strong>"+D("title")+"</strong></label><br><br>"),C=P.Node.create('<div style="position:absolute; top: 0; right: 0;" class="availibility_examus-tab-btns"></div>').appendTo(a),q=P.Node.create('<a href="#" class="btn btn-primary">1</a>').appendTo(C),I=P.Node.create('<a href="#" class="btn btn-secondary">2</a>').appendTo(C),N=P.Node.create('<div class="tab_content">'+i+"</div>").appendTo(a),T=P.Node.create('<div class="tab_content hidden">'+w+"</div>").appendTo(a),e.creating&&(e.mode="normal",e.scheduling_required=!0),e.duration!==undefined&&a.one("input[name=duration]").set("value",e.duration),e.mode!==undefined&&a.one("select[name=mode] option[value="+e.mode+"]").set("selected","selected"),e.identification!==undefined&&a.one("select[name=identification] option[value="+e.identification+"]").set("selected","selected"),e.auto_rescheduling!==undefined&&(n=e.auto_rescheduling?"checked":null,a.one("#"+s).set("checked",n)),e.noprotection!==undefined&&(n=e.noprotection?"checked":null,a.one("#"+c).set("checked",n)),e.istrial!==undefined&&(n=e.istrial?"checked":null,a.one("#"+r).set("checked",n)),e.auxiliarycamera!==undefined&&(n=e.auxiliarycamera?"checked":null,a.one("#"+d).set("checked",n)),e.scheduling_required!==undefined&&(n=e.scheduling_required?"checked":null,a.one("#"+o).set("checked",n)),e.rules===undefined&&(e.rules=this.rules),e.warnings===undefined)e.warnings=this.warnings;else for(x in R=e.warnings,e.warnings=this.warnings,R)e.warnings[x]=R[x];for(O in e.rules)e.rules[O]&&(E=a.one(".rules input[name="+O+"]"))&&E.set("checked","checked");for(V in e.warnings)e.warnings[V]&&(A=a.one(".warnings input[name="+V+"]"))&&A.set("checked","checked");return e.customrules!==undefined&&a.one("#"+u).set("value",e.customrules),e.useragreementurl!==undefined&&a.one("#"+p).set("value",
e.useragreementurl),a.delegate("valuechange",function(){H(function(){M.core_availability.form.update()})},"input,textarea,select"),a.delegate("click",function(){H(function(){M.core_availability.form.update()})},"input[type=checkbox]"),a.delegate("valuechange",function(){var e,i;e=["normal","identification"],i=a.one("select[name=mode]").get("value").trim(),e=0<=e.indexOf(i),a.one("#"+o).set("checked",e)},"#"+l),q.on("click",function(e){e.preventDefault(),L(1)}),I.on("click",function(e){e.preventDefault(),L(2)}),a},M.availability_examus.form.fillValue=function(a,e){var i,n,t;a.duration=e.one("input[name=duration]").get("value").trim(),a.mode=e.one("select[name=mode]").get("value").trim(),a.identification=e.one("select[name=identification]").get("value").trim(),a.auto_rescheduling=e.one("input[name=auto_rescheduling]").get("checked"),a.scheduling_required=e.one("input[name=scheduling_required]").get("checked"),a.istrial=e.one("input[name=istrial]").get("checked"),a.customrules=e.one("textarea[name=customrules]").get("value").trim(),a.noprotection=e.one("input[name=noprotection]").get("checked"),a.useragreementurl=e.one("input[name=useragreementurl]").get("value").trim(),a.auxiliarycamera=e.one("input[name=auxiliarycamera]").get("checked"),a.rules={},i=e.all(".rules input"),P.each(i,function(e){t=e.get("value"),!0===e.get("checked")?a.rules[t]=!0:a.rules[t]=!1}),a.warnings={},n=e.all(".warnings input"),P.each(n,function(e){t=e.get("value"),!0===e.get("checked")?a.warnings[t]=!0:a.warnings[t]=!1}),a.groups=[],i=e.all(".groups input"),P.each(i,function(e){var i=e.get("value");!0===e.get("checked")&&a.groups.push(i)})},M.availability_examus.form.fillErrors=function(e,i){var a={};this.fillValue(a,i),a.duration!==undefined&&new RegExp("^\\d+$").test(a.duration)&&a.duration%30==0||e.push("availability_examus:error_setduration")}},"@VERSION@",{requires:["base","node","event","moodle-core_availability-form"]});