!function(e){function t(r){if(n[r])return n[r].exports;var l=n[r]={i:r,l:!1,exports:{}};return e[r].call(l.exports,l,l.exports,t),l.l=!0,l.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=0)}([function(e,t){var n=this,r=(wp.i18n.__,wp.blocks),l=r.registerBlockType,a=(r.Editable,wp.editor.InspectorControls),s=wp.components,i=s.ToggleControl,u=s.SelectControl,m=s.Spinner;wp.element.createElement;l("pirate-forms/form",{title:pfjs.i10n.plugin,icon:"index-card",category:"common",supports:{html:!1},attributes:{html:{type:"string",default:""},html_changed:{type:"number",default:-1},is_form_loading:{type:"number",default:0},form_id:{type:"number",default:-1},ajax:{type:"string",default:"no"},label:{type:"string",default:pfjs.i10n.loading_form},spinner:{type:"string",default:"pf-form-spinner"},link:{type:"string",default:""},url:{type:"string",default:""}},edit:function(e){var t=function(t){1!==e.attributes.is_form_loading&&(e.setAttributes({is_form_loading:1,spinner:"pf-form-spinner pf-form-loading",link:""}),wp.apiRequest({path:pfjs.url.replace("#",t)}).then(function(r){if(n.unmounting)return e.setAttributes({spinner:"pf-form-spinner",html_changed:0}),r;var l=""!==e.attributes.html&&e.attributes.html!=r.html,a=l?1:0,s=0==t?pfjs.settings.default:pfjs.settings.form.replace("#",t);e.setAttributes({html:r.html,label:"",spinner:"pf-form-spinner",url:s,link:pfjs.i10n.settings,html_changed:a,is_form_loading:0}),jQuery(".pirate-forms-maps-custom").trigger("addCustomSpam"),jQuery(".pirate-forms-google-recaptcha").each(function(){0===jQuery(this).html().length&&jQuery(this).html(pfjs.i10n.captcha)})}))},r=function(n){return e.setAttributes({form_id:n}),n>-1&&t(n),null},l=function(t){return e.setAttributes({ajax:!0===t?"yes":"no"}),null};return-1===e.attributes.form_id&&(1===pfjs.forms.length?r(0):e.setAttributes({label:pfjs.i10n.multiple_forms})),[wp.element.createElement("div",{className:e.className},e.attributes.label),function(){return e.isSelected?pfjs.forms.length>1?wp.element.createElement(a,null,wp.element.createElement(u,{label:pfjs.i10n.select_form,options:pfjs.forms,value:e.attributes.form_id,onChange:r}),wp.element.createElement(i,{label:pfjs.i10n.select_ajax,checked:"yes"==e.attributes.ajax,onChange:l}),wp.element.createElement("div",null,wp.element.createElement("a",{href:e.attributes.url,target:"_new"},e.attributes.link)),wp.element.createElement("div",{className:e.attributes.spinner},wp.element.createElement(m,null))):wp.element.createElement(a,null,wp.element.createElement(i,{label:pfjs.i10n.select_ajax,checked:"yes"==e.attributes.ajax,onChange:l}),wp.element.createElement("div",null,wp.element.createElement("a",{href:e.attributes.url,target:"_new"},e.attributes.link)),wp.element.createElement("div",{className:e.attributes.spinner},wp.element.createElement(m,null))):null}(),wp.element.createElement("div",{className:e.className,dangerouslySetInnerHTML:function(){return{__html:e.attributes.html}}()})]},save:function(e){return e.attributes.html_changed=-1,null}})}]);