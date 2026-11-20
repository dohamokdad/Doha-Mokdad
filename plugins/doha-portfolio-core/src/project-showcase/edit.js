/**
 * Project Showcase block: editor UI.
 * Settings live in the sidebar; the canvas shows the real server-rendered output.
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
	Spinner,
	Disabled,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes, name } ) {
	const { count, columns, projectType, featuredOnly, showMeta, imageCrop } =
		attributes;

	const { types, isLoading } = useSelect( ( select ) => {
		const query = {
			per_page: 100,
			hide_empty: true,
			_fields: 'id,name,slug',
		};
		return {
			types: select( coreStore ).getEntityRecords(
				'taxonomy',
				'project_type',
				query
			),
			isLoading: select( coreStore ).isResolving( 'getEntityRecords', [
				'taxonomy',
				'project_type',
				query,
			] ),
		};
	}, [] );

	const typeOptions = [
		{ label: __( 'All types', 'doha-portfolio' ), value: '' },
		...( types || [] ).map( ( t ) => ( { label: t.name, value: t.slug } ) ),
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Projects', 'doha-portfolio' ) }>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Number of projects', 'doha-portfolio' ) }
						value={ count }
						onChange={ ( value ) =>
							setAttributes( { count: value } )
						}
						min={ 1 }
						max={ 12 }
					/>
					{ isLoading ? (
						<Spinner />
					) : (
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Project type', 'doha-portfolio' ) }
							value={ projectType }
							options={ typeOptions }
							onChange={ ( value ) =>
								setAttributes( { projectType: value } )
							}
						/>
					) }
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Featured projects only',
							'doha-portfolio'
						) }
						checked={ featuredOnly }
						onChange={ ( value ) =>
							setAttributes( { featuredOnly: value } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Layout', 'doha-portfolio' ) }>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Columns', 'doha-portfolio' ) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 1 }
						max={ 4 }
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Screenshot crop', 'doha-portfolio' ) }
						help={ __(
							'Top shows the first screen of each site, like a visitor sees it.',
							'doha-portfolio'
						) }
						value={ imageCrop }
						options={ [
							{
								label: __(
									'Top of the page',
									'doha-portfolio'
								),
								value: 'top',
							},
							{
								label: __( 'Centre', 'doha-portfolio' ),
								value: 'center',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { imageCrop: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Show type and country',
							'doha-portfolio'
						) }
						checked={ showMeta }
						onChange={ ( value ) =>
							setAttributes( { showMeta: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<Disabled>
					<ServerSideRender
						block={ name }
						attributes={ attributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
