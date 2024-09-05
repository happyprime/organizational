import { registerPlugin } from '@wordpress/plugins';
import { TextControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

const PeopleMetaPanel = () => {
	const { meta } = useSelect((select) => ({
		meta: select('core/editor').getEditedPostAttribute('meta'),
	}));

	const { editPost } = useDispatch('core/editor');

	const fields = [
		{ key: 'prefix', label: 'Prefix' },
		{ key: 'first_name', label: 'First name' },
		{ key: 'last_name', label: 'Last name' },
		{ key: 'suffix', label: 'Suffix' },
		{ key: 'title', label: 'Title' },
		{ key: 'title_secondary', label: 'Secondary title' },
		{ key: 'office', label: 'Office' },
		{ key: 'email', label: 'Email' },
		{ key: 'phone', label: 'Phone' },
	];

	const renderTextControl = ({ key, label }) => {
		const metaKey = `organizational_person_${key}`;
		return (
			<TextControl
				key={key}
				label={__(label, 'organizational')}
				value={meta[metaKey]}
				onChange={(value) => {
					editPost({ meta: { [metaKey]: value } });
				}}
			/>
		);
	};

	return (
		<PluginDocumentSettingPanel
			name="people-meta-panel"
			title={__('Person')}
			icon={<></>}
		>
			{fields.map(renderTextControl)}
		</PluginDocumentSettingPanel>
	);
};

registerPlugin('people-meta-panel', {
	render: PeopleMetaPanel,
});
