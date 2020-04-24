( function ( wp ) {
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var el = wp.element.createElement;
	var Toggle = wp.components.ToggleControl;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
 
	var mapSelectToProps = function( select ) {
		return {
			metaFieldValue: select( 'core/editor' ).getEditedPostAttribute( 'meta' )[ 'fbpfwp_2_publish' ],
		}
	}

	var mapDispatchToProps = function( dispatch, props ) {
		return {
			setMetaFieldValue: function( value ) {
				dispatch( 'core/editor' ).editPost(
					{ meta: { fbpfwp_2_publish: value } }
				);
			}
		}
	}
 
	var MetaBlockField = function(props) {
		return el( Toggle, {
			label: 'Post to Facebook Page',
			checked: props.metaFieldValue,
			onChange: function( checked ) {
				props.setMetaFieldValue( checked );
			},
		} );
	}

	var MetaBlockFieldWithData = withSelect( mapSelectToProps )( MetaBlockField );
	var MetaBlockFieldWithDataAndActions = withDispatch( mapDispatchToProps )( MetaBlockFieldWithData );
  
	registerPlugin( 'fbpfwp-toggle', {
		render: function() {
			return el(
				PluginDocumentSettingPanel,
				{
					name: 'fbpfwp-toggle',
					title: 'Facebook',
					icon: 'facebook',
					className: 'fbpfwp-toggle',
				},
				el( MetaBlockFieldWithDataAndActions )
			);
		}
	} );
} )( window.wp );
