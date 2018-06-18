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
                default: 1,
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
                default: __( 'Loading Form' ) + '...',
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
            props.setAttributes( { spinner: 'pf-form-spinner pf-form-loading', link: '' } );
            wp.apiRequest( { path: pfjs.url.replace('#', $id)} )
                .then(
                    (data) => {
                        props.setAttributes( { spinner: 'pf-form-spinner' } );
                        if ( this.unmounting ) {
                            return data;
                        }

                        // check if the new html is different from what was previously saved.
                        if(props.attributes.html === data.html){
                            props.setAttributes( { html_changed: 0 } );
                            return;
                        }
                        
                        if(props.attributes.html !== ''){
                            alert(pfjs.i10n.reload);
                        }

                        var $url = $id == 0 ? pfjs.settings.default : pfjs.settings.form.replace('#', $id);

                        props.setAttributes( { html: data.html, label: '', spinner: 'pf-form-spinner', url: $url, link: pfjs.i10n.settings, html_changed: 0 } );
                        jQuery('.pirate-forms-maps-custom').trigger('addCustomSpam');
                        
                        // when the form is just added, captcha will not show.
                        jQuery('.pirate-forms-google-recaptcha').each(function(){
                            if(jQuery(this).html().length === 0){
                                jQuery(this).html(pfjs.i10n.captcha);
                            }
                        });
                    }
                );
        };

        const innerHTML = () => {
            return {__html: props.attributes.html};
        }

        const onChangeForm = value => {
            props.setAttributes( { form_id: value } );
            if(value > -1){
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

        // load default by default.
        if(props.attributes.form_id == -1){
            onChangeForm(0);
        } else if(props.attributes.html_changed === 1){
            props.setAttributes( { html_changed: 0 } );
            getFormHTML(props.attributes.form_id);
        }

        return [
            <div className={ props.className }>{props.attributes.label}</div>,
            getInspectorControls(),
            <div className={ props.className } dangerouslySetInnerHTML={innerHTML()}></div>,
        ];
    },
    save: props => {
       props.attributes.html_changed = 1;
       return null;
    },
} );