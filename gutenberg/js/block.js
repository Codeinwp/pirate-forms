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
	title: __( 'Pirate Forms' ),
	icon: 'index-card',
	category: 'common',
	supports: {
		html: false,
	},
    attributes: {
            // contains the html of the form.
            html: {
                type: 'string',
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
                        if ( this.unmounting ) {
                            return data;
                        }

                        var $url = $id == 0 ? pfjs.settings.default : pfjs.settings.form.replace('#', $id);

                        props.setAttributes( { html: data.html, label: '', spinner: 'pf-form-spinner', url: $url, link: __('Modify Settings') } );
                        jQuery('.pirate-forms-maps-custom').trigger('addCustomSpam');
                        
                        // when the form is just added, captcha will not show.
                        jQuery('.pirate-forms-google-recaptcha').each(function(){
                            if(jQuery(this).html().length === 0){
                                jQuery(this).html(__('Save and reload the page to see the CAPTCHA'));
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

        // load default by default.
        if(props.attributes.form_id == -1){
            onChangeForm(0);
        }

        return [
            <div className={ props.className }>{props.attributes.label}</div>,
            !! props.isSelected && pfjs.forms.length > 1 && (
                <InspectorControls> 
                    <SelectControl
                        label={__('Select Form')}
                        options={pfjs.forms}
                        value={props.attributes.form_id}
                        onChange={ onChangeForm }
                    />
                    <ToggleControl
                        label={__('Use Ajax to submit form')}
                        checked={props.attributes.ajax == 'yes'}
                        onChange={ onChangeAjax }
                    />
                    <div>
                        <a href={props.attributes.url} target="_new">{props.attributes.link}</a>
                    </div>
                    <div className={props.attributes.spinner}>
                        <Spinner />
                    </div>
                </InspectorControls>
            ),
            <div className={ props.className } dangerouslySetInnerHTML={innerHTML()}></div>,
        ];
    },
    save: props => {
       return null;
    },
} );