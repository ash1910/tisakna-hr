/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
var ManagerPostMessage=function(){return{init:function(b){document.addEventListener?window.addEventListener("message",b,!1):window.attachEvent("onmessage",b)},send:function(b){var f=Object.prototype.toString;fn=b.fn||null;id=b.id||"";target=b.target||window;message=b.message||{id:id};"[object Object]"==f.call(b.message)&&(b.message.id?b.message.id:b.message.id=id,message=b.message);b=JSON.stringify(message,fn);target.postMessage(b,"*")}}},tools={hash:{create:function(b,f){return JSON.stringify(b,
f||null)},parse:function(b,f){return JSON.parse(b,f||null)}},filter4html:function(b){return b.replace(/"/g,"\x26quot;").replace(/'/g,"\x26#146;")},setCookie:function(b,f,g){g=g||{};var c=g.expires;if("number"==typeof c&&c){var d=new Date;d.setTime(d.getTime()+1E3*c);c=g.expires=d}c&&c.toUTCString&&(g.expires=c.toUTCString());f=encodeURIComponent(f);b=b+"\x3d"+f;for(var k in g)b+="; "+k,f=g[k],!0!==f&&(b+="\x3d"+f);document.cookie=b},getCookie:function(b){return(b=document.cookie.match(new RegExp("(?:^|; )"+
b.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g,"\\$1")+"\x3d([^;]*)")))?decodeURIComponent(b[1]):void 0},deleteCookie:function(b){setCookie(b,"",{expires:-1})}},optionsDataObject={},NS={},nameNode=null;NS.targetFromFrame={};NS.wsc_customerId=CKEDITOR.config.wsc_customerId;NS.cust_dic_ids=CKEDITOR.config.wsc_customDictionaryIds;NS.userDictionaryName=CKEDITOR.config.wsc_userDictionaryName;NS.defaultLanguage=CKEDITOR.config.defaultLanguage;NS.LocalizationComing={};
function OptionsConfirm(b){b&&nameNode.setValue("")}
CKEDITOR.dialog.add("checkspell",function(b){function f(a){if(!a)throw"Languages-by-groups list are required for construct selectbox";var e=[],m="",b;for(b in a)for(var d in a[b]){var c=a[b][d];"en_US"==c?m=c:e.push(c)}e.sort();m&&e.unshift(m);return{getCurrentLangGroup:function(e){a:{for(var b in a)for(var m in a[b])if(m.toUpperCase()===e.toUpperCase()){e=b;break a}e=""}return e},setLangList:function(){var e={},b;for(b in a)for(var m in a[b])e[a[b][m]]=m;return e}()}}CKEDITOR.on("dialogDefinition",
function(a){a.data.definition.dialog.on("cancel",function(a){return!1},this,null,-1)});NS.CKNumber=CKEDITOR.tools.getNextNumber();NS.iframeNumber="cke_frame_"+NS.CKNumber;NS.TextAreaNumber="cke_textarea_"+NS.CKNumber;NS.pluginPath=CKEDITOR.getUrl(b.plugins.wsc.path);NS.logotype=DefaultParams.logoPath;NS.templatePath=NS.pluginPath+"dialogs/tmp.html";NS.div_overlay_no_check=null;NS.loadIcon=DefaultParams.iconPath;NS.loadIconEmptyEditor=DefaultParams.iconPathEmptyEditor;NS.LangComparer=new _SP_FCK_LangCompare;
NS.LangComparer.setDefaulLangCode(NS.defaultLanguage);NS.currentLang=b.config.wsc_lang||NS.LangComparer.getSPLangCode(b.langCode);NS.LocalizationButton={ChangeTo:{instance:null,text:"Change to"},ChangeAll:{instance:null,text:"Change All"},IgnoreWord:{instance:null,text:"Ignore word"},IgnoreAllWords:{instance:null,text:"Ignore all words"},Options:{instance:null,text:"Options",optionsDialog:{instance:null}},AddWord:{instance:null,text:"Add word"},FinishChecking:{instance:null,text:"Finish Checking"}};
NS.LocalizationLabel={ChangeTo:{instance:null,text:"Change to"},Suggestions:{instance:null,text:"Suggestions"}};var g=function(a){for(var e in a)a[e].instance.getElement().setText(NS.LocalizationComing[e])},c=function(a){for(var e in a){if(!a[e].instance.setLabel)break;a[e].instance.setLabel(NS.LocalizationComing[e])}};NS.load=!0;NS.cmd={SpellTab:"spell",Thesaurus:"thes",GrammTab:"grammar"};NS.dialog=null;NS.optionNode=null;NS.selectNode=null;NS.grammerSuggest=null;NS.textNode={};NS.iframeMain=null;
NS.dataTemp="";NS.div_overlay=null;NS.textNodeInfo={};NS.selectNode={};NS.selectNodeResponce={};NS.selectingLang=NS.currentLang;NS.langList=null;NS.serverLocationHash=DefaultParams.serviceHost;NS.serverLocation="#server\x3d"+NS.serverLocationHash;NS.langSelectbox=null;NS.banner="";var d=null;iframeOnload=!1;NS.framesetHtml=function(a){return'\x3ciframe src\x3d"'+NS.templatePath+NS.serverLocation+'" id\x3d'+NS.iframeNumber+"_"+a+' frameborder\x3d"0" allowtransparency\x3d"1" style\x3d"width:100%;border: 1px solid #AEB3B9;overflow: auto;background:#fff; border-radius: 3px;"\x3e\x3c/iframe\x3e'};
NS.setIframe=function(a,e){var b=NS.framesetHtml(e);return a.getElement().setHtml(b)};NS.setCurrentIframe=function(a){NS.setIframe(NS.dialog._.contents[a].Content,a)};NS.sendData=function(){var a=NS.dialog._.currentTabId,e=NS.dialog._.contents[a].Content,b,d;NS.setIframe(e,a);NS.dialog.parts.tabs.removeAllListeners();NS.dialog.parts.tabs.on("click",function(c){c=c||window.event;c.data.getTarget().is("a")&&a!=NS.dialog._.currentTabId&&(a=NS.dialog._.currentTabId,e=NS.dialog._.contents[a].Content,b=
NS.iframeNumber+"_"+a,0==e.getElement().$.children.length?(NS.setIframe(e,a),d=document.getElementById(b),NS.targetFromFrame[b]=d.contentWindow):t(NS.targetFromFrame[b],NS.cmd[a]))})};NS.buildOptionSynonyms=function(a){a=NS.selectNodeResponce[a];NS.selectNode.synonyms.clear();for(var e=0;e<a.length;e++)NS.selectNode.synonyms.add(a[e],a[e]);NS.selectNode.synonyms.getInputElement().$.firstChild.selected=!0;NS.textNode.Thesaurus.setValue(NS.selectNode.synonyms.getInputElement().getValue())};NS.buildSelectLang=
function(){var a=new CKEDITOR.dom.element("div"),e=new CKEDITOR.dom.element("select"),b="wscLang"+NS.CKNumber;a.addClass("cke_dialog_ui_input_select");a.setAttribute("role","presentation");a.setStyles({height:"auto",position:"absolute",right:"0",top:"-1px",width:"160px","white-space":"normal"});e.setAttribute("id",b);e.addClass("cke_dialog_ui_input_select");e.setStyles({width:"160px"});a.append(e);return a};NS.buildOptionLang=function(a){var e=document.getElementById("wscLang"+NS.CKNumber),b,d;if(0===
e.options.length)for(var c in a)b=document.createElement("option"),b.setAttribute("value",a[c]),d=document.createTextNode(c),b.appendChild(d),a[c]==NS.selectingLang&&(b.selected=!0),e.appendChild(b);for(a=0;a<e.options.length;a++)e.options[a].value==NS.selectingLang&&(e.options[a].selected=!0)};var k=function(a){var e=document,b=a.target||e.body,d=a.id||"overlayBlock",c=a.opacity||"0.9";a=a.background||"#f1f1f1";var l=e.getElementById(d),f=l||e.createElement("div");f.style.cssText="position: absolute;top:30px;bottom:40px;left:1px;right:1px;z-index: 10020;padding:0;margin:0;background:"+
a+";opacity: "+c+";filter: alpha(opacity\x3d"+100*c+");display: none;";f.id=d;l||b.appendChild(f);return{setDisable:function(){f.style.display="none"},setEnable:function(){f.style.display="block"}}},n=function(a,e,b){var d=new CKEDITOR.dom.element("div"),c=new CKEDITOR.dom.element("input"),l=new CKEDITOR.dom.element("label"),f="wscGrammerSuggest"+a+"_"+e;d.addClass("cke_dialog_ui_input_radio");d.setAttribute("role","presentation");d.setStyles({width:"97%",padding:"5px","white-space":"normal"});c.setAttributes({type:"radio",
value:e,name:"wscGrammerSuggest",id:f});c.setStyles({"float":"left"});c.on("click",function(a){NS.textNode.GrammTab.setValue(a.sender.getValue())});b?c.setAttribute("checked",!0):!1;c.addClass("cke_dialog_ui_radio_input");l.appendText(a);l.setAttribute("for",f);l.setStyles({display:"block","line-height":"16px","margin-left":"18px","white-space":"normal"});d.append(c);d.append(l);return d},q=function(a){var e=new f(a);a=document.getElementById("wscLang"+NS.CKNumber);var b=NS.iframeNumber+"_"+NS.dialog._.currentTabId;
NS.buildOptionLang(e.setLangList);a.onchange=function(a){v[e.getCurrentLangGroup(this.value)]();NS.div_overlay.setEnable();NS.selectingLang=this.value;d.send({message:{changeLang:NS.selectingLang,text:NS.dataTemp},target:NS.targetFromFrame[b],id:"selectionLang_outer__page"})}},l=function(a){if("no_any_suggestions"==a){var e=function(a){a=NS.LocalizationButton[a].instance;a.getElement().hasClass("cke_disabled")?a.getElement().setStyle("color","#a0a0a0"):a.disable()};a="No suggestions";NS.LocalizationButton.ChangeTo.instance.disable();
NS.LocalizationButton.ChangeAll.instance.disable();e("ChangeTo");e("ChangeAll")}else NS.LocalizationButton.ChangeTo.instance.enable(),NS.LocalizationButton.ChangeAll.instance.enable(),NS.LocalizationButton.ChangeTo.instance.getElement().setStyle("color","#333"),NS.LocalizationButton.ChangeAll.instance.getElement().setStyle("color","#333");return a},w={iframeOnload:function(a){NS.div_overlay.setEnable();iframeOnload=!0;a=NS.dialog._.currentTabId;t(NS.targetFromFrame[NS.iframeNumber+"_"+a],NS.cmd[a])},
suggestlist:function(a){delete a.id;NS.div_overlay_no_check.setDisable();r();q(NS.langList);var e=l(a.word),b="";e instanceof Array&&(e=a.word[0]);b=e=e.split(",");selectNode.clear();NS.textNode.SpellTab.setValue(b[0]);for(a=0;a<b.length;a++)selectNode.add(b[a],b[a]);p();NS.div_overlay.setDisable()},grammerSuggest:function(a){delete a.id;delete a.mocklangs;r();var b=a.grammSuggest[0];NS.grammerSuggest.getElement().setHtml("");NS.textNode.GrammTab.reset();NS.textNode.GrammTab.setValue(b);NS.textNodeInfo.GrammTab.getElement().setHtml("");
NS.textNodeInfo.GrammTab.getElement().setText(a.info);a=a.grammSuggest;for(var b=a.length,d=!0,c=0;c<b;c++)NS.grammerSuggest.getElement().append(n(a[c],a[c],d)),d=!1;p();NS.div_overlay.setDisable()},thesaurusSuggest:function(a){delete a.id;delete a.mocklangs;r();NS.selectNodeResponce=a;NS.textNode.Thesaurus.reset();NS.selectNode.categories.clear();for(var b in a)NS.selectNode.categories.add(b,b);a=NS.selectNode.categories.getInputElement().getChildren().$[0].value;NS.selectNode.categories.getInputElement().getChildren().$[0].selected=
!0;NS.buildOptionSynonyms(a);p();NS.div_overlay.setDisable()},finish:function(a){delete a.id;NS.dialog.getContentElement(NS.dialog._.currentTabId,"bottomGroup").getElement().hide();NS.dialog.getContentElement(NS.dialog._.currentTabId,"BlockFinishChecking").getElement().show();NS.div_overlay.setDisable()},settext:function(a){delete a.id;NS.dialog.getParentEditor().focus();NS.dialog.getParentEditor().setData(a.text,NS.dialog.hide())},ReplaceText:function(a){delete a.id;NS.div_overlay.setEnable();NS.dataTemp=
a.text;NS.selectingLang=a.currentLang;window.setTimeout(function(){NS.div_overlay.setDisable()},500);g(NS.LocalizationButton);c(NS.LocalizationLabel)},options_checkbox_send:function(a){delete a.id;a={osp:tools.getCookie("osp"),udn:tools.getCookie("udn"),cust_dic_ids:NS.cust_dic_ids};d.send({message:a,target:NS.targetFromFrame[NS.iframeNumber+"_"+NS.dialog._.currentTabId],id:"options_outer__page"})},getOptions:function(a){var b=a.DefOptions.udn;NS.LocalizationComing=a.DefOptions.localizationButtonsAndText;
NS.langList=a.lang;var c=a.banner;NS.dialog.getContentElement(NS.dialog._.currentTabId,"banner").getElement().setHtml(c);"undefined"==b&&(NS.userDictionaryName?(b=NS.userDictionaryName,c={osp:tools.getCookie("osp"),udn:NS.userDictionaryName,cust_dic_ids:NS.cust_dic_ids,id:"options_dic_send",udnCmd:"create"},d.send({message:c,target:NS.targetFromFrame[frameId]})):b="");tools.setCookie("osp",a.DefOptions.osp);tools.setCookie("udn",b);tools.setCookie("cust_dic_ids",a.DefOptions.cust_dic_ids);d.send({id:"giveOptions"})},
options_dic_send:function(a){a={osp:tools.getCookie("osp"),udn:tools.getCookie("udn"),cust_dic_ids:NS.cust_dic_ids,id:"options_dic_send",udnCmd:tools.getCookie("udnCmd")};d.send({message:a,target:NS.targetFromFrame[NS.iframeNumber+"_"+NS.dialog._.currentTabId]})},data:function(a){delete a.id},giveOptions:function(){},setOptionsConfirmF:function(){OptionsConfirm(!1)},setOptionsConfirmT:function(){OptionsConfirm(!0)},clickBusy:function(){NS.div_overlay.setEnable()},suggestAllCame:function(){NS.div_overlay.setDisable();
NS.div_overlay_no_check.setDisable()},TextCorrect:function(){q(NS.langList)}},x=function(a){a=a||window.event;a=JSON.parse(a.data);w[a.id](a)},h=function(a){NS.div_overlay.setEnable();a=NS.dialog._.currentTabId;var b=NS.iframeNumber+"_"+a,c=NS.textNode[a].getValue();d.send({message:{cmd:this.getElement().getAttribute("title-cmd"),tabId:a,new_word:c},target:NS.targetFromFrame[b],id:"cmd_outer__page"})},t=function(a,b,c,l){b=b||CKEDITOR.config.wsc_cmd||"spell";c=c||NS.dataTemp;d.send({message:{customerId:NS.wsc_customerId,
text:c,txt_ctrl:NS.TextAreaNumber,cmd:b,cust_dic_ids:NS.cust_dic_ids,udn:NS.userDictionaryName,slang:NS.selectingLang,reset_suggest:l||!1},target:a,id:"data_outer__page"});NS.div_overlay.setEnable()},v={superset:function(){NS.dialog.showPage("Thesaurus");NS.dialog.showPage("GrammTab");NS.dialog.showPage("SpellTab")},usual:function(){NS.dialog.hidePage("Thesaurus");NS.dialog.hidePage("GrammTab");NS.dialog.showPage("SpellTab")}},u=function(){var a=new function(a){var b={};return{getCmdByTab:function(c){for(var d in a)b[a[d]]=
d;return b[c]}}}(NS.cmd);NS.dialog.selectPage(a.getCmdByTab(CKEDITOR.config.wsc_cmd));NS.sendData()},p=function(){NS.dialog.getContentElement(NS.dialog._.currentTabId,"bottomGroup").getElement().show()},r=function(){NS.dialog.getContentElement(NS.dialog._.currentTabId,"BlockFinishChecking").getElement().hide()};return{title:b.config.wsc_dialogTitle||b.lang.wsc.title,minWidth:560,minHeight:350,resizable:CKEDITOR.DIALOG_RESIZE_NONE,buttons:[CKEDITOR.dialog.cancelButton],onLoad:function(){d=new ManagerPostMessage;
NS.dialog=this;u();NS.dataTemp=NS.dialog.getParentEditor().getData();d.init(x);NS.div_overlay=new k({opacity:"0.95",background:"#fff url("+NS.loadIcon+") no-repeat 50% 50%",target:this.parts.tabs.getParent().$});NS.div_overlay_no_check=new k({opacity:"1",id:"no_check_over",background:"#fff url("+NS.loadIconEmptyEditor+") no-repeat 50% 50%",target:this.parts.tabs.getParent().$});var a=CKEDITOR.document.getById("cke_dialog_tabs_"+(NS.CKNumber+1));a.setStyle("width","97%");a.append(NS.buildSelectLang())},
onShow:function(){NS.div_overlay.setDisable();u();""==NS.dialog.getParentEditor().getData()&&NS.div_overlay_no_check.setEnable()},onHide:function(){NS.dataTemp=null},contents:[{id:"SpellTab",label:"SpellChecker",accessKey:"S",elements:[{type:"html",id:"banner",label:"banner",html:"\x3cdiv\x3e\x3c/div\x3e"},{type:"html",id:"Content",label:"spellContent",html:"",onLoad:function(){var a=NS.iframeNumber+"_"+NS.dialog._.currentTabId,b=document.getElementById(a);NS.targetFromFrame[a]=b.contentWindow},onShow:function(){NS.dataTemp=
NS.dialog.getParentEditor().getData();NS.div_overlay.setEnable()}},{type:"hbox",id:"bottomGroup",widths:["50%","50%"],children:[{type:"hbox",id:"leftCol",align:"left",width:"50%",children:[{type:"vbox",id:"rightCol1",widths:["50%","50%"],children:[{type:"text",id:"text",label:NS.LocalizationLabel.ChangeTo.text+":",labelLayout:"horizontal",labelStyle:"font: 12px/25px arial, sans-serif;",width:"140px","default":"",onLoad:function(){NS.textNode.SpellTab=this;NS.LocalizationLabel.ChangeTo.instance=this},
onHide:function(){this.reset()}},{type:"hbox",id:"rightCol",align:"right",width:"30%",children:[{type:"vbox",id:"rightCol_col__left",children:[{type:"text",id:"labelSuggestions",label:NS.LocalizationLabel.Suggestions.text+":",onLoad:function(){NS.LocalizationLabel.Suggestions.instance=this;this.getInputElement().hide()}},{type:"html",id:"logo",html:'\x3cimg width\x3d"99" height\x3d"68" border\x3d"0" src\x3d"" title\x3d"WebSpellChecker.net" alt\x3d"WebSpellChecker.net" style\x3d"display: inline-block;"\x3e',
onShow:function(){this.getElement().$.src=NS.logotype;this.getElement().getParent().setStyles({"text-align":"left"})}}]},{type:"select",id:"list_of_suggestions",labelStyle:"font: 12px/25px arial, sans-serif;",size:"6",inputStyle:"width: 140px; height: auto;",items:[["loading..."]],onShow:function(){selectNode=this},onHide:function(){this.clear()},onChange:function(){NS.textNode.SpellTab.setValue(this.getValue())}}]}]}]},{type:"hbox",id:"rightCol",align:"right",width:"50%",children:[{type:"vbox",id:"rightCol_col__left",
widths:["50%","50%","50%","50%"],children:[{type:"button",id:"ChangeTo",label:NS.LocalizationButton.ChangeTo.text,title:"Change to",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id);NS.LocalizationButton.ChangeTo.instance=this},onClick:h},{type:"button",id:"ChangeAll",label:NS.LocalizationButton.ChangeAll.text,title:"Change All",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id);NS.LocalizationButton.ChangeAll.instance=
this},onClick:h},{type:"button",id:"AddWord",label:NS.LocalizationButton.AddWord.text,title:"Add word",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id);NS.LocalizationButton.AddWord.instance=this},onClick:h},{type:"button",id:"FinishChecking",label:NS.LocalizationButton.FinishChecking.text,title:"Finish Checking",style:"width: 100%;margin-top: 9px;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id);NS.LocalizationButton.FinishChecking.instance=
this},onClick:h}]},{type:"vbox",id:"rightCol_col__right",widths:["50%","50%","50%"],children:[{type:"button",id:"IgnoreWord",label:NS.LocalizationButton.IgnoreWord.text,title:"Ignore word",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id);NS.LocalizationButton.IgnoreWord.instance=this},onClick:h},{type:"button",id:"IgnoreAllWords",label:NS.LocalizationButton.IgnoreAllWords.text,title:"Ignore all words",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",
this.id);NS.LocalizationButton.IgnoreAllWords.instance=this},onClick:h},{type:"button",id:"option",label:NS.LocalizationButton.Options.text,title:"Option",style:"width: 100%;",onLoad:function(){NS.LocalizationButton.Options.instance=this},onClick:function(){b.openDialog("options")}}]}]}]},{type:"hbox",id:"BlockFinishChecking",widths:["70%","30%"],onShow:function(){this.getElement().hide()},onHide:p,children:[{type:"hbox",id:"leftCol",align:"left",width:"70%",children:[{type:"vbox",id:"rightCol1",
children:[{type:"html",id:"logo",html:'\x3cimg width\x3d"99" height\x3d"68" border\x3d"0" src\x3d"" title\x3d"WebSpellChecker.net" alt\x3d"WebSpellChecker.net" style\x3d"display: inline-block;"\x3e',onShow:function(){this.getElement().$.src=NS.logotype;this.getElement().getParent().setStyles({"text-align":"center"})}}]}]},{type:"hbox",id:"rightCol",align:"right",width:"30%",children:[{type:"vbox",id:"rightCol_col__left",children:[{type:"button",id:"Option_button",label:NS.LocalizationButton.Options.text,
title:"Option",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:function(){b.openDialog("options")}},{type:"button",id:"FinishChecking",label:NS.LocalizationButton.FinishChecking.text,title:"Finish Checking",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]}]}]}]},{id:"GrammTab",label:"Grammar",accessKey:"G",elements:[{type:"html",id:"banner",label:"banner",html:"\x3cdiv\x3e\x3c/div\x3e"},{type:"html",
id:"Content",label:"GrammarContent",html:"",onShow:function(){var a=NS.iframeNumber+"_"+NS.dialog._.currentTabId,b=document.getElementById(a);NS.targetFromFrame[a]=b.contentWindow}},{type:"vbox",id:"bottomGroup",children:[{type:"hbox",id:"leftCol",widths:["66%","34%"],children:[{type:"vbox",children:[{type:"text",id:"text",label:"Change to:",labelLayout:"horizontal",labelStyle:"font: 12px/25px arial, sans-serif; float: right;margin-right: 80px;",inputStyle:"",width:"200px","default":"",onLoad:function(a){NS.textNode.GrammTab=
this},onHide:function(){this.reset()}},{type:"html",id:"html_text",html:"\x3cdiv style\x3d'min-height: 17px; width: 330px; line-height: 17px; padding: 5px; text-align: left;background: #F1F1F1;color: #595959; white-space: normal!important;'\x3e\x3c/div\x3e",onLoad:function(a){NS.textNodeInfo.GrammTab=this}},{type:"html",id:"radio",html:"",onLoad:function(){NS.grammerSuggest=this}}]},{type:"vbox",children:[{type:"button",id:"ChangeTo",label:"Change to",title:"Change to",style:"width: 133px; float: right;",
onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h},{type:"button",id:"IgnoreWord",label:"Ignore word",title:"Ignore word",style:"width: 133px; float: right;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h},{type:"button",id:"IgnoreAllWords",label:"Ignore Problem",title:"Ignore Problem",style:"width: 133px; float: right;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h},{type:"button",id:"FinishChecking",
label:"Finish Checking",title:"Finish Checking",style:"width: 133px; float: right; margin-top: 9px;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]}]}]},{type:"hbox",id:"BlockFinishChecking",widths:["70%","30%"],onShow:function(){this.getElement().hide()},onHide:p,children:[{type:"hbox",id:"leftCol",align:"left",width:"70%",children:[{type:"vbox",id:"rightCol1",children:[{type:"html",id:"logo",html:'\x3cimg width\x3d"99" height\x3d"68" border\x3d"0" src\x3d"" title\x3d"WebSpellChecker.net" alt\x3d"WebSpellChecker.net" style\x3d"display: inline-block;"\x3e',
onShow:function(){this.getElement().$.src=NS.logotype;this.getElement().getParent().setStyles({"text-align":"center"})}}]}]},{type:"hbox",id:"rightCol",align:"right",width:"30%",children:[{type:"vbox",id:"rightCol_col__left",children:[{type:"button",id:"FinishChecking",label:"Finish Checking",title:"Finish Checking",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]}]}]}]},{id:"Thesaurus",label:"Thesaurus",accessKey:"T",elements:[{type:"html",id:"banner",
label:"banner",html:"\x3cdiv\x3e\x3c/div\x3e"},{type:"html",id:"Content",label:"spellContent",html:"",onShow:function(){var a=NS.iframeNumber+"_"+NS.dialog._.currentTabId,b=document.getElementById(a);NS.targetFromFrame[a]=b.contentWindow}},{type:"vbox",id:"bottomGroup",children:[{type:"hbox",widths:["75%","25%"],children:[{type:"vbox",children:[{type:"hbox",widths:["65%","35%"],children:[{type:"text",id:"ChangeTo",label:"Change to:",labelLayout:"horizontal",inputStyle:"width: 160px;",labelStyle:"font: 12px/25px arial, sans-serif;",
"default":"",onLoad:function(a){NS.textNode.Thesaurus=this},onHide:function(){this.reset()}},{type:"button",id:"ChangeTo",label:"Change to",title:"Change to",style:"width: 121px; margin-top: 1px;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]},{type:"hbox",children:[{type:"select",id:"categories",label:"Categories:",labelStyle:"font: 12px/25px arial, sans-serif;",size:"6",inputStyle:"width: 180px; height: auto;",items:[],onLoad:function(a){NS.selectNode.categories=
this},onHide:function(){this.clear()},onChange:function(){NS.buildOptionSynonyms(this.getValue())}},{type:"select",id:"synonyms",label:"Synonyms:",labelStyle:"font: 12px/25px arial, sans-serif;",size:"6",inputStyle:"width: 180px; height: auto;",items:[],onLoad:function(a){NS.selectNode.synonyms=this},onShow:function(){NS.textNode.Thesaurus.setValue(this.getValue())},onHide:function(){this.clear()},onChange:function(a){NS.textNode.Thesaurus.setValue(this.getValue())}}]}]},{type:"vbox",width:"120px",
style:"margin-top:46px;",children:[{type:"html",id:"logotype",label:"WebSpellChecker.net",html:'\x3cimg width\x3d"99" height\x3d"68" border\x3d"0" src\x3d"" title\x3d"WebSpellChecker.net" alt\x3d"WebSpellChecker.net" style\x3d"display: inline-block;"\x3e',onShow:function(){this.getElement().$.src=NS.logotype;this.getElement().getParent().setStyles({"text-align":"center"})}},{type:"button",id:"FinishChecking",label:"Finish Checking",title:"Finish Checking",style:"width: 121px; float: right; margin-top: 9px;",
onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]}]}]},{type:"hbox",id:"BlockFinishChecking",widths:["70%","30%"],onShow:function(){this.getElement().hide()},onHide:p,children:[{type:"hbox",id:"leftCol",align:"left",width:"70%",children:[{type:"vbox",id:"rightCol1",children:[{type:"html",id:"logo",html:'\x3cimg width\x3d"99" height\x3d"68" border\x3d"0" src\x3d"" title\x3d"WebSpellChecker.net" alt\x3d"WebSpellChecker.net" style\x3d"display: inline-block;"\x3e',onShow:function(){this.getElement().$.src=
NS.logotype;this.getElement().getParent().setStyles({"text-align":"center"})}}]}]},{type:"hbox",id:"rightCol",align:"right",width:"30%",children:[{type:"vbox",id:"rightCol_col__left",children:[{type:"button",id:"FinishChecking",label:"Finish Checking",title:"Finish Checking",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onClick:h}]}]}]}]}]}});
CKEDITOR.dialog.add("options",function(b){var f=new ManagerPostMessage,g=null,c={},d={},k=null,n=null;tools.getCookie("udn");tools.getCookie("osp");b=function(b){n=this.getElement().getAttribute("title-cmd");b=[];b[0]=d.IgnoreAllCapsWords;b[1]=d.IgnoreWordsNumbers;b[2]=d.IgnoreMixedCaseWords;b[3]=d.IgnoreDomainNames;b=b.toString().replace(/,/g,"");tools.setCookie("osp",b);tools.setCookie("udnCmd",n?n:"ignore");"delete"!=n&&tools.setCookie("udn",""==nameNode.getValue()?"":nameNode.getValue());f.send({id:"options_dic_send"})};
var q=function(){k.getElement().setHtml(NS.LocalizationComing.error);k.getElement().show()};return{title:NS.LocalizationComing.Options,minWidth:430,minHeight:130,resizable:CKEDITOR.DIALOG_RESIZE_NONE,contents:[{id:"OptionsTab",label:"Options",accessKey:"O",elements:[{type:"hbox",id:"options_error",children:[{type:"html",style:"display: block;text-align: center;white-space: normal!important; font-size: 12px;color:red",html:"\x3cdiv\x3e\x3c/div\x3e",onShow:function(){k=this}}]},{type:"vbox",id:"Options_content",
children:[{type:"hbox",id:"Options_manager",widths:["52%","48%"],children:[{type:"fieldset",label:"Spell Checking Options",style:"border: none;margin-top: 13px;padding: 10px 0 10px 10px",onShow:function(){this.getInputElement().$.children[0].innerHTML=NS.LocalizationComing.SpellCheckingOptions},children:[{type:"vbox",id:"Options_checkbox",children:[{type:"checkbox",id:"IgnoreAllCapsWords",label:"Ignore All-Caps Words",labelStyle:"margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;",
style:"float:left; min-height: 16px;","default":"",onClick:function(){d[this.id]=0==this.getValue()?0:1}},{type:"checkbox",id:"IgnoreWordsNumbers",label:"Ignore Words with Numbers",labelStyle:"margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;",style:"float:left; min-height: 16px;","default":"",onClick:function(){d[this.id]=0==this.getValue()?0:1}},{type:"checkbox",id:"IgnoreMixedCaseWords",label:"Ignore Mixed-Case Words",labelStyle:"margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;",
style:"float:left; min-height: 16px;","default":"",onClick:function(){d[this.id]=0==this.getValue()?0:1}},{type:"checkbox",id:"IgnoreDomainNames",label:"Ignore Domain Names",labelStyle:"margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;",style:"float:left; min-height: 16px;","default":"",onClick:function(){d[this.id]=0==this.getValue()?0:1}}]}]},{type:"vbox",id:"Options_DictionaryName",children:[{type:"text",id:"DictionaryName",style:"margin-bottom: 10px",
label:"Dictionary Name:",labelLayout:"vertical",labelStyle:"font: 12px/25px arial, sans-serif;","default":"",onLoad:function(){nameNode=this;var b=NS.userDictionaryName?NS.userDictionaryName:(tools.getCookie("udn"),this.getValue());this.setValue(b)},onShow:function(){nameNode=this;var b=tools.getCookie("udn")?tools.getCookie("udn"):this.getValue();this.setValue(b);this.setLabel(NS.LocalizationComing.DictionaryName)},onHide:function(){this.reset()}},{type:"hbox",id:"Options_buttons",children:[{type:"vbox",
id:"Options_leftCol_col",widths:["50%","50%"],children:[{type:"button",id:"create",label:"Create",title:"Create",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onShow:function(){this.getElement().setText(NS.LocalizationComing.Create)},onClick:b},{type:"button",id:"restore",label:"Restore",title:"Restore",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onShow:function(){this.getElement().setText(NS.LocalizationComing.Restore)},
onClick:b}]},{type:"vbox",id:"Options_rightCol_col",widths:["50%","50%"],children:[{type:"button",id:"rename",label:"Rename",title:"Rename",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onShow:function(){this.getElement().setText(NS.LocalizationComing.Rename)},onClick:b},{type:"button",id:"delete",label:"Remove",title:"Remove",style:"width: 100%;",onLoad:function(){this.getElement().setAttribute("title-cmd",this.id)},onShow:function(){this.getElement().setText(NS.LocalizationComing.Remove)},
onClick:b}]}]}]}]},{type:"hbox",id:"Options_text",children:[{type:"html",style:"text-align: justify;margin-top: 15px;white-space: normal!important; font-size: 12px;color:#777;",html:"\x3cdiv\x3e"+NS.LocalizationComing.OptionsTextIntro+"\x3c/div\x3e",onShow:function(){this.getElement().setText(NS.LocalizationComing.OptionsTextIntro)}}]}]}]}],buttons:[CKEDITOR.dialog.okButton,CKEDITOR.dialog.cancelButton],onOk:function(){var b=[];b[0]=d.IgnoreAllCapsWords;b[1]=d.IgnoreWordsNumbers;b[2]=d.IgnoreMixedCaseWords;
b[3]=d.IgnoreDomainNames;b=b.toString().replace(/,/g,"");tools.setCookie("osp",b);tools.setCookie("udn",nameNode.getValue());f.send({id:"options_checkbox_send"});k.getElement().hide();k.getElement().setHtml(" ")},onLoad:function(){g=this;f.init(q);c.IgnoreAllCapsWords=g.getContentElement("OptionsTab","IgnoreAllCapsWords");c.IgnoreWordsNumbers=g.getContentElement("OptionsTab","IgnoreWordsNumbers");c.IgnoreMixedCaseWords=g.getContentElement("OptionsTab","IgnoreMixedCaseWords");c.IgnoreDomainNames=g.getContentElement("OptionsTab",
"IgnoreDomainNames")},onShow:function(){strToArr=tools.getCookie("osp").split("");d.IgnoreAllCapsWords=strToArr[0];d.IgnoreWordsNumbers=strToArr[1];d.IgnoreMixedCaseWords=strToArr[2];d.IgnoreDomainNames=strToArr[3];0==d.IgnoreAllCapsWords?c.IgnoreAllCapsWords.setValue("",!1):c.IgnoreAllCapsWords.setValue("checked",!1);0==d.IgnoreWordsNumbers?c.IgnoreWordsNumbers.setValue("",!1):c.IgnoreWordsNumbers.setValue("checked",!1);0==d.IgnoreMixedCaseWords?c.IgnoreMixedCaseWords.setValue("",!1):c.IgnoreMixedCaseWords.setValue("checked",
!1);0==d.IgnoreDomainNames?c.IgnoreDomainNames.setValue("",!1):c.IgnoreDomainNames.setValue("checked",!1);d.IgnoreAllCapsWords=0==c.IgnoreAllCapsWords.getValue()?0:1;d.IgnoreWordsNumbers=0==c.IgnoreWordsNumbers.getValue()?0:1;d.IgnoreMixedCaseWords=0==c.IgnoreMixedCaseWords.getValue()?0:1;d.IgnoreDomainNames=0==c.IgnoreDomainNames.getValue()?0:1;c.IgnoreAllCapsWords.getElement().$.lastChild.innerHTML=NS.LocalizationComing.IgnoreAllCapsWords;c.IgnoreWordsNumbers.getElement().$.lastChild.innerHTML=
NS.LocalizationComing.IgnoreWordsWithNumbers;c.IgnoreMixedCaseWords.getElement().$.lastChild.innerHTML=NS.LocalizationComing.IgnoreMixedCaseWords;c.IgnoreDomainNames.getElement().$.lastChild.innerHTML=NS.LocalizationComing.IgnoreDomainNames}}});