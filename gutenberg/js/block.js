const { __ } = wp.i18n;
const {
	registerBlockType,
	Editable,
} = wp.blocks;

const {
	InspectorControls,
} = wp.editor;

const { 
    SelectControl,
    Spinner,
} = wp.components;

var el = wp.element.createElement;

registerBlockType( 'pirate-forms/form', {
	title: __( 'Pirate Forms' ),
	icon: 'index-card',
	category: 'common',
	supports: {
		html: true,
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
    },
    edit: props => {
        const getFormHTML = ($id) => {
            props.setAttributes( { spinner: 'pf-form-spinner pf-form-loading' } );
            wp.apiRequest( { path: pfjs.url.replace('#', $id)} )
                .then(
                    (data) => {
                        if ( this.unmounting ) {
                            return data;
                        }
                        props.setAttributes( { html: data.html, label: '', spinner: 'pf-form-spinner' } );
                        jQuery('.pirate-forms-maps-custom').trigger('addCustomSpam');
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