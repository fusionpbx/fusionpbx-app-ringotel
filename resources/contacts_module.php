<?php
/*
    Ringotel Integration for FusionPBX - shared Contacts module

    Renders the organization's shared contacts (the "Manage contacts" list in the
    Ringotel portal): list, search, create, edit, delete, plus bulk actions
    (select all / per row, bulk edit, bulk delete, CSV export, CSV import).
    Every bulk action asks for confirmation first.

    Included by index.php: as the Contacts tab, or as the only content for users
    who hold "ringotel_contacts" but not "ringotel_extensions".
    Requires: $application_directory. Self-contained: does not depend on the
    JavaScript of index.php, and resolves the organization on the server side.
*/

if (!isset($application_directory)) {
	$application_directory = pathinfo(__DIR__ . '/..')['basename'];
}

echo '<style>';
echo "	#contacts_module > div:first-child { flex-wrap: wrap; gap: 0.25rem 0.5rem; }";
echo "	#contacts_module .contacts_header_button { color: #007bff; background-color: transparent; border-color: transparent; font-weight: bold; white-space: nowrap; }";
echo "	#contacts_table th { font-size: unset; vertical-align: middle; white-space: nowrap; }";
echo "	#contacts_table td { word-break: break-word; }";
echo "	#contacts_table .contact_select, #contacts_select_all { transform: scale(1.2); cursor: pointer; }";
echo "	#contacts_table tr.contact_selected { background-color: #e8f2fc; }";
echo "	#contacts_bulk_bar { display: none; flex-wrap: wrap; align-items: center; gap: 0.25rem 0.75rem; padding: 0.5rem 0.75rem; margin-bottom: 0.5rem; background: #f4f8fc; border: 1px solid #cfe2f3; border-radius: 4px; }";
echo "	#contacts_bulk_bar .btn { padding: 2px 10px; font-size: 10pt; font-weight: 600; }";
echo "	#contactModal .modal-body, #contactsBulkEditModal .modal-body, #contactsConfirmModal .modal-body { max-height: 70vh; overflow-y: auto; }";
echo "	#contacts_confirm_list { max-height: 30vh; overflow-y: auto; margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 10pt; color: #555; }";
echo "	@media (max-width: 768px) {";
echo "		#contacts_table th:nth-child(4), #contacts_table td:nth-child(4),";
echo "		#contacts_table th:nth-child(6), #contacts_table td:nth-child(6) { display: none; }";
echo "		#contacts_search { max-width: 100% !important; }";
echo "	}";
echo '</style>';

echo '		<div id="contacts_module">';
echo '			<div style="display: flex; padding: 1rem 0rem;flex-direction: row;justify-content: space-between;align-items: center;">';
echo '				<h4 class="card-title" style="font-weight: 400; padding: 0.5rem 0.2rem;margin: 0;" id="contacts">Contacts <span id="contacts_count"></span></h4>';
echo '				<input type="text" id="contacts_search" class="form-control" placeholder="Search contacts..." aria-label="Search contacts" style="margin-left: auto;max-width: 16rem;height: 2rem;font-size: 10pt;">';
echo '				<input type="file" id="contacts_import_file" accept=".csv,text/csv" style="display: none;">';
echo '				<button id="import_contacts_button" class="btn btn-primary contacts_header_button" disabled title="Import contacts from a CSV file"><i class="fa fa-upload" aria-hidden="true"></i> Import CSV</button>';
echo '				<button id="export_contacts_button" class="btn btn-primary contacts_header_button" disabled title="Export the selected contacts (or all) to a CSV file"><i class="fa fa-download" aria-hidden="true"></i> Export CSV</button>';
echo '	     		<button id="create_contact_button" class="btn btn-primary contacts_header_button" disabled>';
echo '	     			<span id="create_contact_text">+ Add Contact</span>';
echo '				</button>';
echo '			</div>';
echo '			<div id="contacts_loading" style="display: flex;align-items: center;gap: 0.5rem;color: #777;padding: 0rem 0.2rem 1rem 0.2rem;">';
echo ' 				<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>';
echo ' 				<span>Contacts loading...</span>';
echo '			</div>';
echo '			<div id="contacts_error" style="display: none;color: #c31919;padding: 0rem 0.2rem 0.5rem 0.2rem;font-size: 10pt;"></div>';
echo '			<div id="contacts_notice" style="display: none;color: #1b7a2f;padding: 0rem 0.2rem 0.5rem 0.2rem;font-size: 10pt;"></div>';
echo '			<div id="contacts_empty_note" style="display: none;color: #777;padding: 0rem 0.2rem 1rem 0.2rem;">No shared contacts yet. Contacts added here appear in the contact book of every user in this organization.</div>';
// bulk action bar (visible while at least one contact is selected)
echo '			<div id="contacts_bulk_bar">';
echo '				<span style="font-weight: 600;"><span id="contacts_selected_count">0</span> selected</span>';
echo '				<button id="bulk_edit_contacts_button" class="btn btn-outline-primary" title="Change organization, position or department of the selected contacts"><i class="fa fa-edit" aria-hidden="true"></i> Edit selected</button>';
echo '				<button id="bulk_export_contacts_button" class="btn btn-outline-primary" title="Export the selected contacts to a CSV file"><i class="fa fa-download" aria-hidden="true"></i> Export selected</button>';
echo '				<button id="bulk_delete_contacts_button" class="btn btn-outline-danger" title="Delete the selected contacts"><i class="fa fa-trash" aria-hidden="true"></i> Delete selected</button>';
echo '				<button id="clear_contacts_selection_button" class="btn btn-outline-secondary" style="margin-left: auto;">Clear selection</button>';
echo '			</div>';
echo '			<div id="group_exist_contacts" style="display: none;overflow-x: auto;">';
echo '				<table class="table table-hover" id="contacts_table" style="width: 100%;margin: 0;">';
echo '				  <thead>';
echo '				    <tr>';
echo '				      <th scope="col" style="width: 2rem;"><input type="checkbox" id="contacts_select_all" aria-label="Select all visible contacts" title="Select all visible contacts"></th>';
echo '				      <th scope="col">Name</th>';
echo '				      <th scope="col">Mobile</th>';
echo '				      <th scope="col">Phone</th>';
echo '				      <th scope="col">Email</th>';
echo '				      <th scope="col">Organization</th>';
echo '				      <th scope="col"></th>';
echo '				    </tr>';
echo '				  </thead>';
echo '				  <tbody id="contacts_table_body">';
// here we receive the contacts
echo '				  </tbody>';
echo '				</table>';
echo '			</div>';
echo '		</div>';

?>

<script>
	//							  \\
	///						     \\\
	//// CONTACTS (shared organization directory) \\\\
	///							 \\\
	//							  \\
	// Same list as "Manage contacts" in the Ringotel portal: contacts owned by the
	// organization that show up in the contact book of every user.

	let CONTACTS = [];
	const SELECTED_CONTACTS = new Set();
	const CONTACTS_SERVICE = "/app/<?php echo $application_directory; ?>/service.php?method=";
	const CONTACTS_BATCH_SIZE = 40; // contacts per request for bulk import / update (keeps requests well under PHP input limits)
	const CONTACT_LIST_FIELDS = ['mobile', 'phone', 'fax', 'email'];
	const CONTACT_TEXT_FIELDS = ['organization', 'position', 'department', 'about'];
	const CONTACT_CSV_COLUMNS = ['name', 'mobile', 'phone', 'fax', 'email', 'organization', 'position', 'department', 'about'];

	const escapeHtml = (value) => String(value ?? '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');

	// info fields may come back as a string or a list; always work with a list
	const contactValues = (info, key) => {
		const value = info?.[key];
		if (Array.isArray(value)) {
			return value.filter((v) => v !== null && v !== undefined && String(v).trim() !== '').map((v) => String(v));
		}
		if (value === null || value === undefined || String(value).trim() === '') {
			return [];
		}
		return [String(value)];
	};
	const contactOrganization = (info) => info?.organization || info?.company || '';

	// full, flat copy of a contact as the service expects it (lists as arrays)
	const contactToRequest = (contact) => {
		const info = contact?.info || {};
		const data = { id: contact?.id, name: contact?.name || '' };
		CONTACT_LIST_FIELDS.forEach((key) => { data[key] = contactValues(info, key); });
		data.organization = contactOrganization(info);
		data.position = info?.position || '';
		data.department = info?.department || '';
		data.about = info?.about || '';
		return data;
	};

	// responses are parsed as-is so names with quotes or non-latin characters survive
	const parseContactsResponse = (response) => {
		if (typeof response !== 'string') {
			return response;
		}
		try {
			return JSON.parse(response);
		} catch (error) {
			return { success: false, data: 'Unexpected response from the server.' };
		}
	};
	const contactsResponseError = (parsed) => (parsed && parsed.success === false) ? (parsed.data || 'Unexpected error.') : null;

	const showContactsError = (message) => {
		if (message) {
			$('#contacts_notice').hide().text('');
			$('#contacts_error').text(message).fadeIn(200);
		} else {
			$('#contacts_error').hide().text('');
		}
	};
	const showContactsNotice = (message) => {
		if (message) {
			$('#contacts_error').hide().text('');
			$('#contacts_notice').text(message).fadeIn(200);
		} else {
			$('#contacts_notice').hide().text('');
		}
	};

	// POST helper: resolves with the parsed response or rejects with an error message
	const contactsRequest = (method, data) => new Promise((resolve, reject) => {
		$.ajax({
			url: CONTACTS_SERVICE + method,
			type: "post",
			cache: false,
			data
		}).then((response) => {
			const parsed = parseContactsResponse(response);
			const error = contactsResponseError(parsed);
			if (error) {
				reject(error);
			} else {
				resolve(parsed);
			}
		}, () => reject('The request failed. Please try again.'));
	});

	// run a bulk request in batches; resolves with the list of parsed responses
	const contactsBatchedRequest = async (method, key, items) => {
		const responses = [];
		for (let i = 0; i < items.length; i += CONTACTS_BATCH_SIZE) {
			const data = {};
			data[key] = items.slice(i, i + CONTACTS_BATCH_SIZE);
			responses.push(await contactsRequest(method, data));
		}
		return responses;
	};

	const contactField = (id, label, hint) => (`
		<div class="paddingBottomEc contact_field" style="padding-bottom: 15px;padding-right: 5px;padding-left: 5px;">
			<div class="contact_field_label" style="font-size: 9.5pt;color: #666;">${label}</div>
			<input id="${id}" type="text" class="form-control" style="border: 0;border-bottom: 1px solid #80808063;border-radius: 0;" placeholder="${hint || label}" aria-label="${label}">
		</div>`);

	////// Contact MODAL (create + edit) //////
	const contactModalTemplate = () => (`
		<div class="modal hide fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true" style="display: none;">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="contactModalLabel">Create Contact</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" style="width: auto;">
						<form id="contact_form" onsubmit="return false;">
							<input type="hidden" id="contact_id" value="">
							${contactField('contact_name', 'Name *')}
							${contactField('contact_mobile', 'Mobile', 'Separate multiple numbers with commas')}
							${contactField('contact_phone', 'Phone', 'Separate multiple numbers with commas')}
							${contactField('contact_fax', 'Fax', 'Separate multiple numbers with commas')}
							${contactField('contact_email', 'Email', 'Separate multiple addresses with commas')}
							${contactField('contact_organization', 'Organization')}
							${contactField('contact_position', 'Position')}
							${contactField('contact_department', 'Department')}
							${contactField('contact_about', 'About')}
						</form>
						<div id="contact_error" style="display: none;color: #c31919;padding: 0 5px;font-size: 10pt;"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<button id="save_contact_button" class="btn btn-primary" role="button" style="color: white;width: 9rem;">
							<span id="save_contact_text">Save</span>
							<span id="save_contact_loading" style="display: none;">
								<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
								Saving...
							</span>
						</button>
					</div>
				</div>
			</div>
		</div>`);

	////// Confirmation MODAL (every bulk action goes through this) //////
	const contactsConfirmModalTemplate = () => (`
		<div class="modal hide fade" id="contactsConfirmModal" tabindex="-1" role="dialog" aria-labelledby="contactsConfirmModalLabel" aria-hidden="true" style="display: none;">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="contactsConfirmModalLabel">Please confirm</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" style="width: auto;">
						<div id="contacts_confirm_message"></div>
						<ul id="contacts_confirm_list"></ul>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<button id="contacts_confirm_button" class="btn btn-primary" role="button" style="color: white;min-width: 9rem;">Confirm</button>
					</div>
				</div>
			</div>
		</div>`);

	// returns a Promise<boolean>: true when the user confirmed
	const confirmContactsAction = ({ title, message, names = [], confirmLabel = 'Confirm', danger = false }) => new Promise((resolve) => {
		const $modal = $('#contactsConfirmModal');
		let decided = false;
		$('#contactsConfirmModalLabel').text(title || 'Please confirm');
		$('#contacts_confirm_message').html(message || '');
		const shown = names.slice(0, 25);
		const more = names.length - shown.length;
		$('#contacts_confirm_list').html(
			shown.map((name) => `<li>${escapeHtml(name)}</li>`).join('') +
			(more > 0 ? `<li style="list-style: none;color: #888;">... and ${more} more</li>` : '')
		);
		$('#contacts_confirm_button')
			.text(confirmLabel)
			.removeClass('btn-primary btn-danger')
			.addClass(danger ? 'btn-danger' : 'btn-primary')
			.off('click')
			.on('click', () => {
				decided = true;
				$modal.modal('hide');
				resolve(true);
			});
		$modal.off('hidden.bs.modal').on('hidden.bs.modal', () => {
			if (!decided) resolve(false);
		});
		$modal.modal('show');
	});

	////// Bulk edit MODAL //////
	const bulkEditRow = (key, label) => (`
		<div class="paddingBottomEc" style="padding-bottom: 15px;padding-right: 5px;padding-left: 5px;">
			<div style="font-size: 9.5pt;color: #666;">${label}</div>
			<div style="display: flex;gap: 0.5rem;align-items: center;">
				<select id="bulk_mode_${key}" class="form-control bulk_mode" data-key="${key}" style="max-width: 9rem;">
					<option value="keep" selected>Keep as is</option>
					<option value="set">Set to</option>
					<option value="clear">Clear</option>
				</select>
				<input id="bulk_value_${key}" type="text" class="form-control" disabled placeholder="${label}" aria-label="${label}">
			</div>
		</div>`);

	const contactsBulkEditModalTemplate = () => (`
		<div class="modal hide fade" id="contactsBulkEditModal" tabindex="-1" role="dialog" aria-labelledby="contactsBulkEditModalLabel" aria-hidden="true" style="display: none;">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="contactsBulkEditModalLabel">Edit selected contacts</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" style="width: auto;">
						<div style="font-size: 10pt;color: #555;padding: 0 5px 12px 5px;">The change is applied to <b><span id="bulk_edit_count">0</span></b> selected contact(s). Fields left on "Keep as is" are not touched.</div>
						<form id="bulk_edit_form" onsubmit="return false;">
							${bulkEditRow('organization', 'Organization')}
							${bulkEditRow('position', 'Position')}
							${bulkEditRow('department', 'Department')}
						</form>
						<div id="bulk_edit_error" style="display: none;color: #c31919;padding: 0 5px;font-size: 10pt;"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<button id="bulk_edit_apply_button" class="btn btn-primary" role="button" style="color: white;width: 9rem;">Continue</button>
					</div>
				</div>
			</div>
		</div>`);

	const ContactRowTemplate = (contact) => {
		const info = contact?.info || {};
		const id = escapeHtml(contact?.id);
		const selected = SELECTED_CONTACTS.has(String(contact?.id));
		return (`
			<tr class="contact_row${selected ? ' contact_selected' : ''}" data-id="${id}">
				<td style="vertical-align: middle;"><input type="checkbox" class="contact_select" data-id="${id}" aria-label="Select contact"${selected ? ' checked' : ''}></td>
				<td class="contact_cell_name" data-id="${id}" style="vertical-align: middle;font-size: 11pt;font-weight: 500;color: #007bff;cursor: pointer;">${escapeHtml(contact?.name)}</td>
				<td style="vertical-align: middle;font-size: 10.5pt;">${escapeHtml(contactValues(info, 'mobile').join(', '))}</td>
				<td style="vertical-align: middle;font-size: 10.5pt;">${escapeHtml(contactValues(info, 'phone').join(', '))}</td>
				<td style="vertical-align: middle;font-size: 10.5pt;">${escapeHtml(contactValues(info, 'email').join(', '))}</td>
				<td style="vertical-align: middle;font-size: 10.5pt;">${escapeHtml(contactOrganization(info))}</td>
				<td style="vertical-align: middle;white-space: nowrap;text-align: right;">
					<span class="editContact" title="Edit contact" data-id="${id}" style="padding: 8px;color: #2196f3;cursor: pointer;"><i class="fa fa-edit" aria-hidden="true"></i></span>
					<span class="deleteContact" title="Delete contact" data-id="${id}" style="padding: 8px;color: #c31919;cursor: pointer;"><i class="fa fa-trash" aria-hidden="true"></i></span>
				</td>
			</tr>`);
	};

	const findContact = (id) => CONTACTS.find((contact) => String(contact?.id) === String(id));
	const selectedContacts = () => CONTACTS.filter((contact) => SELECTED_CONTACTS.has(String(contact?.id)));

	const contactMatchesSearch = (contact, query) => {
		if (!query) return true;
		const info = contact?.info || {};
		const haystack = [
			contact?.name,
			contactOrganization(info),
			...contactValues(info, 'mobile'),
			...contactValues(info, 'phone'),
			...contactValues(info, 'fax'),
			...contactValues(info, 'email')
		].join(' ').toLowerCase();
		return haystack.includes(query);
	};
	const visibleContacts = () => {
		const query = ($('#contacts_search').val() || '').trim().toLowerCase();
		return CONTACTS.filter((contact) => contactMatchesSearch(contact, query));
	};

	// selection bar + "select all" checkbox state
	const updateSelectionState = () => {
		// drop ids that no longer exist
		[...SELECTED_CONTACTS].forEach((id) => { if (!findContact(id)) SELECTED_CONTACTS.delete(id); });
		const count = SELECTED_CONTACTS.size;
		$('#contacts_selected_count').text(count);
		$('#contacts_bulk_bar').css('display', count > 0 ? 'flex' : 'none');
		const visible = visibleContacts();
		const visibleSelected = visible.filter((contact) => SELECTED_CONTACTS.has(String(contact?.id))).length;
		const all = $('#contacts_select_all')[0];
		if (all) {
			all.checked = visible.length > 0 && visibleSelected === visible.length;
			all.indeterminate = visibleSelected > 0 && visibleSelected < visible.length;
		}
		$('.contact_row').each((k, row) => {
			$(row).toggleClass('contact_selected', SELECTED_CONTACTS.has(String(row.getAttribute('data-id'))));
		});
	};

	const renderContacts = () => {
		const list = visibleContacts();
		$('#contacts_count').text(CONTACTS.length ? `(${CONTACTS.length})` : '');
		if (CONTACTS.length === 0) {
			$('#contacts_table_body').html('');
			$('#group_exist_contacts').hide();
			$('#contacts_empty_note').show();
		} else {
			$('#contacts_empty_note').hide();
			if (list.length === 0) {
				$('#contacts_table_body').html('<tr><td colspan="7" style="color: #777;">No contacts match the search.</td></tr>');
			} else {
				$('#contacts_table_body').html(list.map(ContactRowTemplate).join(''));
			}
			$('#group_exist_contacts').show();
		}
		$('#export_contacts_button').attr('disabled', CONTACTS.length === 0);
		contactRowEvents();
		updateSelectionState();
	};

	// GET CONTACTS (the organization is resolved on the server from the current domain)
	const getContacts = () => {
		$('#create_contact_button, #import_contacts_button, #export_contacts_button').attr('disabled', true);
		showContactsError('');
		return $.ajax({
			url: CONTACTS_SERVICE + 'get_contacts',
			type: "get",
			cache: false
		}).then((response) => {
			const parsed = parseContactsResponse(response);
			const error = contactsResponseError(parsed);
			$('#contacts_loading').hide();
			if (error) {
				CONTACTS = [];
				showContactsError(error);
				return CONTACTS;
			}
			CONTACTS = Array.isArray(parsed?.result) ? parsed.result : [];
			renderContacts();
			$('#create_contact_button, #import_contacts_button').attr('disabled', false);
			return CONTACTS;
		}, () => {
			$('#contacts_loading').hide();
			showContactsError('Could not load the contacts.');
		});
	};

	const openContactModal = (id) => {
		const contact = id ? findContact(id) : null;
		const info = contact?.info || {};
		$('#contactModalLabel').text(contact ? 'Edit Contact' : 'Create Contact');
		$('#contact_id').val(contact?.id || '');
		$('#contact_name').val(contact?.name || '');
		$('#contact_mobile').val(contactValues(info, 'mobile').join(', '));
		$('#contact_phone').val(contactValues(info, 'phone').join(', '));
		$('#contact_fax').val(contactValues(info, 'fax').join(', '));
		$('#contact_email').val(contactValues(info, 'email').join(', '));
		$('#contact_organization').val(contactOrganization(info));
		$('#contact_position').val(info?.position || '');
		$('#contact_department').val(info?.department || '');
		$('#contact_about').val(info?.about || '');
		$('#contact_error').hide().text('');
		$('#contactModal').modal('show');
		setTimeout(() => $('#contact_name').trigger('focus'), 400);
	};

	const setContactSaving = (saving) => {
		$('#save_contact_button').attr('disabled', saving);
		if (saving) {
			$('#save_contact_text').hide();
			$('#save_contact_loading').show();
		} else {
			$('#save_contact_loading').hide();
			$('#save_contact_text').show();
		}
	};

	// CREATE / UPDATE CONTACT
	const saveContact = () => {
		const id = $('#contact_id').val();
		const data = {
			name: ($('#contact_name').val() || '').trim(),
			mobile: $('#contact_mobile').val(),
			phone: $('#contact_phone').val(),
			fax: $('#contact_fax').val(),
			email: $('#contact_email').val(),
			organization: $('#contact_organization').val(),
			position: $('#contact_position').val(),
			department: $('#contact_department').val(),
			about: $('#contact_about').val()
		};
		if (id) {
			data.id = id;
		}
		if (!data.name) {
			$('#contact_error').text('Contact name is required.').show();
			$('#contact_name').trigger('focus');
			return;
		}
		$('#contact_error').hide().text('');
		setContactSaving(true);
		contactsRequest(id ? 'update_contact' : 'create_contact', data).then(() => {
			setContactSaving(false);
			$('#contactModal').modal('hide');
			showContactsNotice(id ? 'Contact updated.' : 'Contact created.');
			getContacts();
		}, (error) => {
			setContactSaving(false);
			$('#contact_error').text(error).show();
		});
	};

	// DELETE CONTACT (single)
	const deleteContact = async (id) => {
		const contact = findContact(id);
		if (!contact) return;
		const confirmed = await confirmContactsAction({
			title: 'Delete contact',
			message: 'Delete this contact from the shared directory? It is removed from the contact book of every user.',
			names: [contact.name || ''],
			confirmLabel: 'Delete',
			danger: true
		});
		if (!confirmed) return;
		showContactsError('');
		$(`.contact_row[data-id="${id}"]`).css('opacity', 0.5);
		contactsRequest('delete_contact', { id }).then(() => {
			SELECTED_CONTACTS.delete(String(id));
			showContactsNotice('Contact deleted.');
			getContacts();
		}, (error) => {
			$(`.contact_row[data-id="${id}"]`).css('opacity', 1);
			showContactsError(error);
		});
	};

	////// BULK ACTIONS //////

	// DELETE SELECTED
	const bulkDeleteContacts = async () => {
		const contacts = selectedContacts();
		if (contacts.length === 0) return;
		const confirmed = await confirmContactsAction({
			title: 'Delete selected contacts',
			message: `Delete <b>${contacts.length}</b> contact(s) from the shared directory? They are removed from the contact book of every user. This cannot be undone.`,
			names: contacts.map((contact) => contact.name || ''),
			confirmLabel: `Delete ${contacts.length} contact(s)`,
			danger: true
		});
		if (!confirmed) return;
		showContactsError('');
		$('#contacts_bulk_bar .btn').attr('disabled', true);
		try {
			await contactsBatchedRequest('delete_contact', 'ids', contacts.map((contact) => String(contact.id)));
			SELECTED_CONTACTS.clear();
			showContactsNotice(`${contacts.length} contact(s) deleted.`);
		} catch (error) {
			showContactsError(error);
		}
		$('#contacts_bulk_bar .btn').attr('disabled', false);
		getContacts();
	};

	// EDIT SELECTED (organization / position / department)
	const openBulkEditModal = () => {
		const contacts = selectedContacts();
		if (contacts.length === 0) return;
		$('#bulk_edit_count').text(contacts.length);
		$('.bulk_mode').val('keep').trigger('change');
		$('#bulk_value_organization, #bulk_value_position, #bulk_value_department').val('');
		$('#bulk_edit_error').hide().text('');
		$('#contactsBulkEditModal').modal('show');
	};

	const bulkEditContacts = async () => {
		const contacts = selectedContacts();
		const changes = {};
		const summary = [];
		['organization', 'position', 'department'].forEach((key) => {
			const mode = $(`#bulk_mode_${key}`).val();
			const label = key.charAt(0).toUpperCase() + key.slice(1);
			if (mode === 'set') {
				const value = ($(`#bulk_value_${key}`).val() || '').trim();
				if (value === '') {
					changes.__invalid = `${label}: enter a value or choose "Clear".`;
					return;
				}
				changes[key] = value;
				summary.push(`${label} &rarr; <b>${escapeHtml(value)}</b>`);
			} else if (mode === 'clear') {
				changes[key] = '';
				summary.push(`${label} &rarr; <i>cleared</i>`);
			}
		});
		if (changes.__invalid) {
			$('#bulk_edit_error').text(changes.__invalid).show();
			return;
		}
		if (summary.length === 0) {
			$('#bulk_edit_error').text('Nothing to change: every field is set to "Keep as is".').show();
			return;
		}
		$('#bulk_edit_error').hide().text('');
		$('#contactsBulkEditModal').modal('hide');
		const confirmed = await confirmContactsAction({
			title: 'Update selected contacts',
			message: `Apply to <b>${contacts.length}</b> contact(s):<br>${summary.join('<br>')}`,
			names: contacts.map((contact) => contact.name || ''),
			confirmLabel: `Update ${contacts.length} contact(s)`
		});
		if (!confirmed) return;
		showContactsError('');
		$('#contacts_bulk_bar .btn').attr('disabled', true);
		const payload = contacts.map((contact) => Object.assign(contactToRequest(contact), changes));
		try {
			await contactsBatchedRequest('update_contacts', 'contacts', payload);
			showContactsNotice(`${contacts.length} contact(s) updated.`);
		} catch (error) {
			showContactsError(error);
		}
		$('#contacts_bulk_bar .btn').attr('disabled', false);
		getContacts();
	};

	////// CSV EXPORT //////
	const csvCell = (value) => {
		const text = String(value ?? '');
		const needsQuotes = text.includes('"') || text.includes(',') || text.includes('\r') || text.includes('\n');
		return needsQuotes ? '"' + text.split('"').join('""') + '"' : text;
	};
	const contactsToCsv = (contacts) => {
		const lines = [CONTACT_CSV_COLUMNS.join(',')];
		contacts.forEach((contact) => {
			const row = contactToRequest(contact);
			lines.push(CONTACT_CSV_COLUMNS.map((key) => {
				const value = row[key];
				return csvCell(Array.isArray(value) ? value.join(', ') : value);
			}).join(','));
		});
		return lines.join('\r\n') + '\r\n';
	};
	const exportContacts = async (contacts, scopeLabel) => {
		if (contacts.length === 0) return;
		const confirmed = await confirmContactsAction({
			title: 'Export contacts',
			message: `Download <b>${contacts.length}</b> ${scopeLabel} contact(s) as a CSV file? Columns: ${CONTACT_CSV_COLUMNS.join(', ')}. Multiple numbers or addresses are separated by commas inside one cell.`,
			confirmLabel: 'Download CSV'
		});
		if (!confirmed) return;
		const blob = new Blob(['\uFEFF' + contactsToCsv(contacts)], { type: 'text/csv;charset=utf-8;' });
		const link = document.createElement('a');
		const stamp = new Date().toISOString().slice(0, 10);
		link.href = URL.createObjectURL(blob);
		link.download = `ringotel-contacts-${stamp}.csv`;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		setTimeout(() => URL.revokeObjectURL(link.href), 1000);
		showContactsNotice(`${contacts.length} contact(s) exported.`);
	};

	////// CSV IMPORT //////
	// RFC 4180 style parser: quoted cells, doubled quotes, newlines inside quotes
	const parseCsv = (text) => {
		const rows = [];
		let row = [];
		let cell = '';
		let quoted = false;
		for (let i = 0; i < text.length; i++) {
			const ch = text[i];
			if (quoted) {
				if (ch === '"') {
					if (text[i + 1] === '"') { cell += '"'; i++; }
					else { quoted = false; }
				} else {
					cell += ch;
				}
			} else if (ch === '"') {
				quoted = true;
			} else if (ch === ',') {
				row.push(cell); cell = '';
			} else if (ch === '\n' || ch === '\r') {
				if (ch === '\r' && text[i + 1] === '\n') i++;
				row.push(cell); cell = '';
				rows.push(row); row = [];
			} else {
				cell += ch;
			}
		}
		if (cell !== '' || row.length > 0) {
			row.push(cell);
			rows.push(row);
		}
		return rows.filter((r) => r.some((c) => String(c).trim() !== ''));
	};

	// map CSV rows to contacts using the same headers as the Ringotel portal import
	// (name or firstname+lastname, mobile, phone, fax, email, organization/company, position, department, about;
	//  a list header may repeat in several columns, and a cell may hold several comma separated values)
	const csvToContacts = (rows) => {
		if (rows.length < 2) return { contacts: [], skipped: 0, error: 'The file has no data rows.' };
		const headers = rows[0].map((h) => String(h).trim().toLowerCase().replace(/^\uFEFF/, ''));
		const aliases = { company: 'organization', organisation: 'organization', 'e-mail': 'email', cell: 'mobile', notes: 'about', note: 'about', title: 'position' };
		const known = new Set(['name', 'firstname', 'lastname', ...CONTACT_LIST_FIELDS, ...CONTACT_TEXT_FIELDS]);
		const columns = headers.map((h) => aliases[h] || h);
		if (!columns.includes('name') && !columns.includes('firstname') && !columns.includes('lastname')) {
			return { contacts: [], skipped: 0, error: 'The first line must contain the column headings, including "name" (or "firstname" and "lastname").' };
		}
		const contacts = [];
		let skipped = 0;
		rows.slice(1).forEach((cells) => {
			const contact = { name: '' };
			CONTACT_LIST_FIELDS.forEach((key) => { contact[key] = []; });
			CONTACT_TEXT_FIELDS.forEach((key) => { contact[key] = ''; });
			let firstname = '', lastname = '';
			columns.forEach((key, index) => {
				if (!known.has(key)) return;
				const value = String(cells[index] ?? '').trim();
				if (value === '') return;
				if (key === 'name') contact.name = value;
				else if (key === 'firstname') firstname = value;
				else if (key === 'lastname') lastname = value;
				else if (CONTACT_LIST_FIELDS.includes(key)) contact[key].push(...value.split(',').map((v) => v.trim()).filter((v) => v));
				else contact[key] = value;
			});
			if (!contact.name) contact.name = [firstname, lastname].filter((v) => v).join(' ');
			if (!contact.name) { skipped++; return; }
			contacts.push(contact);
		});
		return { contacts, skipped, error: null };
	};

	const importContactsFile = (file) => {
		if (!file) return;
		const reader = new FileReader();
		reader.onerror = () => showContactsError('Could not read the file.');
		reader.onload = async () => {
			const { contacts, skipped, error } = csvToContacts(parseCsv(String(reader.result || '')));
			if (error) { showContactsError(error); return; }
			if (contacts.length === 0) { showContactsError('No contacts found in the file (every row is missing a name).'); return; }
			const confirmed = await confirmContactsAction({
				title: 'Import contacts',
				message: `Create <b>${contacts.length}</b> contact(s) from "${escapeHtml(file.name)}" in the shared directory?` +
					(skipped > 0 ? `<br><span style="color: #a66;">${skipped} row(s) without a name will be skipped.</span>` : '') +
					'<br>Existing contacts are not checked for duplicates.',
				names: contacts.map((contact) => contact.name),
				confirmLabel: `Import ${contacts.length} contact(s)`
			});
			if (!confirmed) return;
			showContactsError('');
			$('#import_contacts_button').attr('disabled', true);
			try {
				await contactsBatchedRequest('import_contacts', 'contacts', contacts);
				showContactsNotice(`${contacts.length} contact(s) imported.`);
			} catch (requestError) {
				showContactsError(requestError);
			}
			getContacts();
		};
		reader.readAsText(file);
	};

	////// EVENTS //////
	const contactRowEvents = () => {
		$('.editContact, .contact_cell_name').off('click').on('click', (el) => {
			openContactModal(el.currentTarget.getAttribute('data-id'));
		});
		$('.deleteContact').off('click').on('click', (el) => {
			deleteContact(el.currentTarget.getAttribute('data-id'));
		});
		$('.contact_select').off('change').on('change', (el) => {
			const id = String(el.currentTarget.getAttribute('data-id'));
			if (el.currentTarget.checked) SELECTED_CONTACTS.add(id); else SELECTED_CONTACTS.delete(id);
			updateSelectionState();
		});
	};

	// [Init] Contacts
	$(function () {
		$('body').append(contactModalTemplate());
		$('body').append(contactsConfirmModalTemplate());
		$('body').append(contactsBulkEditModalTemplate());
		$('#create_contact_button').on('click', (() => openContactModal(null)));
		$('#save_contact_button').on('click', saveContact);
		$('#contacts_search').on('keyup', renderContacts);
		$('#contact_form').on('keydown', ((e) => {
			if (e.key === 'Enter' && e.target?.tagName === 'INPUT') {
				e.preventDefault();
				saveContact();
			}
		}));
		// selection
		$('#contacts_select_all').on('change', ((e) => {
			visibleContacts().forEach((contact) => {
				if (e.currentTarget.checked) SELECTED_CONTACTS.add(String(contact.id)); else SELECTED_CONTACTS.delete(String(contact.id));
			});
			$('.contact_select').each((k, box) => { box.checked = SELECTED_CONTACTS.has(String(box.getAttribute('data-id'))); });
			updateSelectionState();
		}));
		$('#clear_contacts_selection_button').on('click', (() => {
			SELECTED_CONTACTS.clear();
			$('.contact_select').each((k, box) => { box.checked = false; });
			updateSelectionState();
		}));
		// bulk actions (each one confirms first)
		$('#bulk_delete_contacts_button').on('click', bulkDeleteContacts);
		$('#bulk_edit_contacts_button').on('click', openBulkEditModal);
		$('#bulk_edit_apply_button').on('click', bulkEditContacts);
		$('.bulk_mode').on('change', ((e) => {
			const key = e.currentTarget.getAttribute('data-key');
			const set = e.currentTarget.value === 'set';
			$(`#bulk_value_${key}`).attr('disabled', !set);
			if (set) $(`#bulk_value_${key}`).trigger('focus');
		}));
		$('#bulk_export_contacts_button').on('click', (() => exportContacts(selectedContacts(), 'selected')));
		$('#export_contacts_button').on('click', (() => {
			const selected = selectedContacts();
			if (selected.length > 0) exportContacts(selected, 'selected'); else exportContacts(CONTACTS, '');
		}));
		$('#import_contacts_button').on('click', (() => $('#contacts_import_file').trigger('click')));
		$('#contacts_import_file').on('change', ((e) => {
			const file = e.currentTarget.files && e.currentTarget.files[0];
			e.currentTarget.value = '';
			importContactsFile(file);
		}));
		getContacts();
	});
</script>
