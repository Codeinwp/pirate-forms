const { __ } = wp.i18n;
const {
	registerBlockType,
	Editable,
} = wp.blocks;
var el = wp.element.createElement;

registerBlockType( 'pirate-forms/default', {
	title: __( 'Pirate Forms - Default Form' ),
	icon: 'index-card',
	category: 'common',
	supports: {
		html: true,
	},
	edit: props => {
        return [
                <div className={ props.className } dangerouslySetInnerHTML={{__html: pfjs.html}}>
                </div>
        ];
    },
	save: props => {
       return null;
    },
} );