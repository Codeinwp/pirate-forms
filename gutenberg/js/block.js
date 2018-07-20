const { __ } = wp.i18n;
const {
	registerBlockType,
	Editable,
} = wp.blocks;

const {
	InspectorControls,
} = wp.editor;

const { 
    ToggleControl,
    SelectControl,
    Spinner,
} = wp.components;

var el = wp.element.createElement;

const consoleLog = msg => {
    //console.log(msg);
}

// return true if the saved html form needs to be compared with the actual shortcode form (and reloaded).
const checkIfSavedFormHasChanged = () => {
    return false;
}

registerBlockType( 'pirate-forms/form', {
	title: pfjs.i10n.plugin,
	icon: 'index-card',
	category: 'common',
	supports: {
		html: false,
	},
    attributes: {
            // contains the html of the form.
            html: {
                type: 'string',
                default: '',
            },
            // tracks if html has changed from the saved html.
            html_changed: {
                type: 'number',
                default: -1,
            },
            is_form_loading: {
                type: 'number',
                default: 0,
            },
            // contains the form id of the form.
            form_id: {
                type: 'number',
                default: -1,
            },
            // indicates whether this is an ajax form.
            ajax: {
                type: 'string',
                default: 'no',
            },
            // the label to show in gutenberg.
            label: {
                type: 'string',
                default: pfjs.i10n.loading_form,
            },
            // the class of the spinner container.
            spinner: {
                type: 'string',
                default: 'pf-form-spinner',
            },
            // link to settings - name of the link.
            link: {
                type: 'string',
                default: '',
            },
            // link to settings.
            url: {
                type: 'string',
                default: '',
            },
    },
    edit: props => {
        const getFormHTML = ($id) => {
            if(props.attributes.is_form_loading === 1){
                return;
            }
            props.setAttributes( { is_form_loading: 1, spinner: 'pf-form-spinner pf-form-loading', link: '' } );
            wp.apiRequest( { path: pfjs.url.replace('#', $id)} )
                .then(
                    (data) => {
                        consoleLog('inside api for getFormHTML');
                        if ( this.unmounting ) {
                        consoleLog('unmounting');
                            props.setAttributes( { spinner: 'pf-form-spinner', html_changed: 0 } );
                            return data;
                        }

                        var $show_alert = props.attributes.html !== '' && props.attributes.html != data.html;
                        var $html_changed = $show_alert ? 1 : 0;

                        var $url = $id == 0 ? pfjs.settings.default : pfjs.settings.form.replace('#', $id);

                        props.setAttributes( { html: data.html, label: '', spinner: 'pf-form-spinner', url: $url, link: pfjs.i10n.settings, html_changed: $html_changed, is_form_loading: 0 } );
                        consoleLog('changed html_changed to ' + $html_changed);
                        jQuery('.pirate-forms-maps-custom').trigger('addCustomSpam');
                        
                        // when the form is just added, captcha will not show.
                        jQuery('.pirate-forms-google-recaptcha').each(function(){
                            if(jQuery(this).html().length === 0){
                                jQuery(this).html(pfjs.i10n.captcha);
                            }
                        });

                        if($show_alert && checkIfSavedFormHasChanged()){
                            consoleLog(pfjs.i10n.reload);
                            alert(pfjs.i10n.reload);
                        }
                    }
                );
        };

        const innerHTML = () => {
            return {__html: props.attributes.html};
        }

        const onChangeForm = value => {
            props.setAttributes( { form_id: value } );
            if(value > -1){
            consoleLog('calling getFormHTML from onChangeForm');
                getFormHTML(value);
            }
            return null;
        }

        const onChangeAjax = value => {
            props.setAttributes( { ajax: ( value === true ? 'yes' : 'no' ) } );
            return null;
        }

        const getInspectorControls = () => {
            if(!! props.isSelected){
                if(pfjs.forms.length > 1){
                    return <InspectorControls> 
                        <SelectControl
                            label={pfjs.i10n.select_form}
                            options={pfjs.forms}
                            value={props.attributes.form_id}
                            onChange={ onChangeForm }
                        />
                        <ToggleControl
                            label={pfjs.i10n.select_ajax}
                            checked={props.attributes.ajax == 'yes'}
                            onChange={ onChangeAjax }
                        />
                        <div>
                            <a href={props.attributes.url} target="_new">{props.attributes.link}</a>
                        </div>
                        <div className={props.attributes.spinner}>
                            <Spinner />
                        </div>
                    </InspectorControls>;
                }
                return <InspectorControls> 
                        <ToggleControl
                            label={pfjs.i10n.select_ajax}
                            checked={props.attributes.ajax == 'yes'}
                            onChange={ onChangeAjax }
                        />
                        <div>
                            <a href={props.attributes.url} target="_new">{props.attributes.link}</a>
                        </div>
                        <div className={props.attributes.spinner}>
                            <Spinner />
                        </div>
                    </InspectorControls>;
            }
            return null;
        }

        if(props.attributes.form_id === -1){
            // new block.
            if(pfjs.forms.length === 1){
                // default.
                consoleLog('calling onChangeForm');
                onChangeForm(0);
            }else{
                // prompt user to select form.
                props.setAttributes( { label: pfjs.i10n.multiple_forms } );
            }
        }else if(checkIfSavedFormHasChanged() && props.attributes.is_form_loading === 0 && props.attributes.html_changed === -1) {
            // THIS IS THE PROBLEMATIC PORTION WHERE html_changed BECOMES -1 AND THIS PORTION IS FIRED REPEATEDLY.
            // html_changed BECOMES 0 FOR AN INSTANT BEFORE save AGAIN MAKES IT -1 THUS REPEATING THE LOOP.
            props.setAttributes( { is_form_loading: 1 } );
            consoleLog('calling onChangeForm from main with value of props.attributes.is_form_loading = ' + props.attributes.is_form_loading);
            onChangeForm(props.attributes.form_id);
        }

        return [
            <div className={ props.className }>{props.attributes.label}</div>,
            getInspectorControls(),
            <div className={ props.className } dangerouslySetInnerHTML={innerHTML()}></div>,
        ];
    },
    save: props => {
       consoleLog("saving");
       props.attributes.html_changed = -1;
       return null;
    },
} );