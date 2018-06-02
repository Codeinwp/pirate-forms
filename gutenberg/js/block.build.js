/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

var _this = this;

var __ = wp.i18n.__;
var _wp$blocks = wp.blocks,
    registerBlockType = _wp$blocks.registerBlockType,
    Editable = _wp$blocks.Editable;
var InspectorControls = wp.editor.InspectorControls;
var _wp$components = wp.components,
    ToggleControl = _wp$components.ToggleControl,
    SelectControl = _wp$components.SelectControl,
    Spinner = _wp$components.Spinner;


var el = wp.element.createElement;

registerBlockType('pirate-forms/form', {
    title: __('Pirate Forms'),
    icon: 'index-card',
    category: 'common',
    supports: {
        html: false
    },
    attributes: {
        // contains the html of the form.
        html: {
            type: 'string'
        },
        // contains the form id of the form.
        form_id: {
            type: 'number',
            default: -1
        },
        // indicates whether this is an ajax form.
        ajax: {
            type: 'string',
            default: 'no'
        },
        // the label to show in gutenberg.
        label: {
            type: 'string',
            default: __('Loading Form') + '...'
        },
        // the class of the spinner container.
        spinner: {
            type: 'string',
            default: 'pf-form-spinner'
        },
        // link to settings - name of the link.
        link: {
            type: 'string',
            default: ''
        },
        // link to settings.
        url: {
            type: 'string',
            default: ''
        }
    },
    edit: function edit(props) {
        var getFormHTML = function getFormHTML($id) {
            props.setAttributes({ spinner: 'pf-form-spinner pf-form-loading', link: '' });
            wp.apiRequest({ path: pfjs.url.replace('#', $id) }).then(function (data) {
                if (_this.unmounting) {
                    return data;
                }

                var $url = $id == 0 ? pfjs.settings.default : pfjs.settings.form.replace('#', $id);

                props.setAttributes({ html: data.html, label: '', spinner: 'pf-form-spinner', url: $url, link: __('Modify Settings') });
                jQuery('.pirate-forms-maps-custom').trigger('addCustomSpam');

                // when the form is just added, captcha will not show.
                jQuery('.pirate-forms-google-recaptcha').each(function () {
                    if (jQuery(this).html().length === 0) {
                        jQuery(this).html(__('Save and reload the page to see the CAPTCHA'));
                    }
                });
            });
        };

        var innerHTML = function innerHTML() {
            return { __html: props.attributes.html };
        };

        var onChangeForm = function onChangeForm(value) {
            props.setAttributes({ form_id: value });
            if (value > -1) {
                getFormHTML(value);
            }
            return null;
        };

        var onChangeAjax = function onChangeAjax(value) {
            props.setAttributes({ ajax: value === true ? 'yes' : 'no' });
            return null;
        };

        // load default by default.
        if (props.attributes.form_id == -1) {
            onChangeForm(0);
        }

        return [wp.element.createElement(
            'div',
            { className: props.className },
            props.attributes.label
        ), !!props.isSelected && pfjs.forms.length > 1 && wp.element.createElement(
            InspectorControls,
            null,
            wp.element.createElement(SelectControl, {
                label: __('Select Form'),
                options: pfjs.forms,
                value: props.attributes.form_id,
                onChange: onChangeForm
            }),
            wp.element.createElement(ToggleControl, {
                label: __('Use Ajax to submit form'),
                checked: props.attributes.ajax == 'yes',
                onChange: onChangeAjax
            }),
            wp.element.createElement(
                'div',
                null,
                wp.element.createElement(
                    'a',
                    { href: props.attributes.url, target: '_new' },
                    props.attributes.link
                )
            ),
            wp.element.createElement(
                'div',
                { className: props.attributes.spinner },
                wp.element.createElement(Spinner, null)
            )
        ), wp.element.createElement('div', { className: props.className, dangerouslySetInnerHTML: innerHTML() })];
    },
    save: function save(props) {
        return null;
    }
});

/***/ })
/******/ ]);