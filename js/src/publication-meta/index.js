import { registerPlugin } from '@wordpress/plugins';
import { DatePicker, TextControl, Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { registerBlockBindingsSource } from '@wordpress/blocks';

const DateControl = ({ metaKey, label, value }) => {
	const [isDatePickerOpen, setIsDatePickerOpen] = useState(false);
	const { editPost } = useDispatch('core/editor');

	let date = null;
	let formattedDate = '';

	if (value) {
		date = new Date(value * 1000);
		formattedDate = date.toLocaleDateString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		});
	}

	return (
		<>
			<TextControl
				label={__(label, 'organizational')}
				value={formattedDate}
				onClick={() => setIsDatePickerOpen(true)}
			/>
			{value && (
				<Button
					variant="secondary"
					onClick={() => {
						// Unset meta key
						editPost({ meta: { [metaKey]: null } });
					}}
				>
					Clear date
				</Button>
			)}
			{isDatePickerOpen && (
				<DatePicker
					currentDate={date}
					onChange={(value) => {
						const date = new Date(value);
						editPost({
							meta: { [metaKey]: date.getTime() / 1000 },
						});
						setIsDatePickerOpen(false);
					}}
				/>
			)}
		</>
	);
};

const PublicationMetaPanel = () => {
	const { meta } = useSelect((select) => ({
		meta: select('core/editor').getEditedPostAttribute('meta'),
	}));

	const { editPost } = useDispatch('core/editor');

	const fields = [
		{ key: 'name', label: 'Journal Name', type: 'textField' },
		{ key: 'issue', label: 'Issue', type: 'textField' },
		{ key: 'authors', label: 'Authors', type: 'textField' },
		{ key: 'url', label: 'URL', type: 'textField' },
		{ key: 'date', label: 'Date', type: 'dateField' },
	];

	const renderDateControl = ({ key, label }) => {
		const metaKey = `organizational_publication_${key}`;

		let value = meta[metaKey] || null;

		return (
			<DateControl
				key={key}
				metaKey={metaKey}
				label={label}
				value={value}
			/>
		);
	};

	const renderControl = ({ key, label, type }) => {
		const metaKey = `organizational_publication_${key}`;

		switch (type) {
			case 'textField':
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
			case 'dateField':
				return renderDateControl({ key, label });
		}
	};

	return (
		<PluginDocumentSettingPanel
			name="publication-meta-panel"
			title={__('Publication Data')}
			icon={<></>}
		>
			{fields.map(renderControl)}
		</PluginDocumentSettingPanel>
	);
};

registerPlugin('publication-meta-panel', {
	render: PublicationMetaPanel,
});
