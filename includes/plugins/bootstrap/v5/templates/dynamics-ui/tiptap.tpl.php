<?php

function textarea($input_name, $input_value, $options)
{
// 2. Unique Identifier for JS variable
	$js_id = str_replace(['-', ' '], '_', $options['input_id']);
	
	// 1. Load Assets (Ensuring paths are correct)
	fusion_load_script(DYNAMICS . 'assets/tiptap/popper.min.js');
	fusion_load_script(DYNAMICS . 'assets/tiptap/tippy-bundle.umd.min.js');
	fusion_load_script(DYNAMICS . 'assets/tiptap/tiptap-core-full.js');
	fusion_load_script(DYNAMICS . 'assets/tiptap/tiptap-styles.css', 'css');
	
	// 1. Prepare Content for JS (Escape backticks to prevent script breaks)
	// 1. Prepare Content for JS (Escape backticks to prevent script breaks)
	// 1. Base64 encode the content to ensure it never breaks JS syntax

// 1. Safely encode the content
	$b64_content = base64_encode($input_value ?: '<p></p>');

// 2. The HTML UI (No Vue syntax, fixed IDs, added type="button" to prevent form submits)
	$html = '
	<div class="tiptap-wrapper" id="wrapper_' . $js_id . '">
		
		<textarea id="'.$options['input_id'].'" name="'.$options['input_name'].'" style="display: none;">'.$options['input_value'].'</textarea>
		
		<div class="tiptap-bubble-menu control-group" style="opacity:0;">
			<div class="button-group">
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Undo" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().undo().run()" data-cmd="undo" aria-label="Undo" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.70711 3.70711C10.0976 3.31658 10.0976 2.68342 9.70711 2.29289C9.31658 1.90237 8.68342 1.90237 8.29289 2.29289L3.29289 7.29289C2.90237 7.68342 2.90237 8.31658 3.29289 8.70711L8.29289 13.7071C8.68342 14.0976 9.31658 14.0976 9.70711 13.7071C10.0976 13.3166 10.0976 12.6834 9.70711 12.2929L6.41421 9H14.5C15.0909 9 15.6761 9.1164 16.2221 9.34254C16.768 9.56869 17.2641 9.90016 17.682 10.318C18.0998 10.7359 18.4313 11.232 18.6575 11.7779C18.8836 12.3239 19 12.9091 19 13.5C19 14.0909 18.8836 14.6761 18.6575 15.2221C18.4313 15.768 18.0998 16.2641 17.682 16.682C17.2641 17.0998 16.768 17.4313 16.2221 17.6575C15.6761 17.8836 15.0909 18 14.5 18H11C10.4477 18 10 18.4477 10 19C10 19.5523 10.4477 20 11 20H14.5C15.3536 20 16.1988 19.8319 16.9874 19.5052C17.7761 19.1786 18.4926 18.6998 19.0962 18.0962C19.6998 17.4926 20.1786 16.7761 20.5052 15.9874C20.8319 15.1988 21 14.3536 21 13.5C21 12.6464 20.8319 11.8012 20.5052 11.0126C20.1786 10.2239 19.6998 9.50739 19.0962 8.90381C18.4926 8.30022 17.7761 7.82144 16.9874 7.49478C16.1988 7.16813 15.3536 7 14.5 7H6.41421L9.70711 3.70711Z" fill="currentColor"></path></svg>
				</button>
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Redo" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().redo().run()" data-cmd="redo" aria-label="Redo" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.7071 2.29289C15.3166 1.90237 14.6834 1.90237 14.2929 2.29289C13.9024 2.68342 13.9024 3.31658 14.2929 3.70711L17.5858 7H9.5C7.77609 7 6.12279 7.68482 4.90381 8.90381C3.68482 10.1228 3 11.7761 3 13.5C3 14.3536 3.16813 15.1988 3.49478 15.9874C3.82144 16.7761 4.30023 17.4926 4.90381 18.0962C6.12279 19.3152 7.77609 20 9.5 20H13C13.5523 20 14 19.5523 14 19C14 18.4477 13.5523 18 13 18H9.5C8.30653 18 7.16193 17.5259 6.31802 16.682C5.90016 16.2641 5.56869 15.768 5.34254 15.2221C5.1164 14.6761 5 14.0909 5 13.5C5 12.3065 5.47411 11.1619 6.31802 10.318C7.16193 9.47411 8.30653 9 9.5 9H17.5858L14.2929 12.2929C13.9024 12.6834 13.9024 13.3166 14.2929 13.7071C14.6834 14.0976 15.3166 14.0976 15.7071 13.7071L20.7071 8.70711C21.0976 8.31658 21.0976 7.68342 20.7071 7.29289L15.7071 2.29289Z" fill="currentColor"></path></svg>
				</button>
			</div>
			<div class="tiptap-separator" data-orientation="vertical" role="none"></div>
			<div class="button-group">
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Bold" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleBold().run()" data-cmd="bold" aria-label="Bold" aria-pressed="false" tabindex="-1" >
					<svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6 2.5C5.17157 2.5 4.5 3.17157 4.5 4V20C4.5 20.8284 5.17157 21.5 6 21.5H15C16.4587 21.5 17.8576 20.9205 18.8891 19.8891C19.9205 18.8576 20.5 17.4587 20.5 16C20.5 14.5413 19.9205 13.1424 18.8891 12.1109C18.6781 11.9 18.4518 11.7079 18.2128 11.5359C19.041 10.5492 19.5 9.29829 19.5 8C19.5 6.54131 18.9205 5.14236 17.8891 4.11091C16.8576 3.07946 15.4587 2.5 14 2.5H6ZM14 10.5C14.663 10.5 15.2989 10.2366 15.7678 9.76777C16.2366 9.29893 16.5 8.66304 16.5 8C16.5 7.33696 16.2366 6.70107 15.7678 6.23223C15.2989 5.76339 14.663 5.5 14 5.5H7.5V10.5H14ZM7.5 18.5V13.5H15C15.663 13.5 16.2989 13.7634 16.7678 14.2322C17.2366 14.7011 17.5 15.337 17.5 16C17.5 16.663 17.2366 17.2989 16.7678 17.7678C16.2989 18.2366 15.663 18.5 15 18.5H7.5Z" fill="currentColor"></path></svg>
				</button>
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Italic" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleItalic().run()" data-cmd="italic" aria-label="Italic" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M15.0222 3H19C19.5523 3 20 3.44772 20 4C20 4.55228 19.5523 5 19 5H15.693L10.443 19H14C14.5523 19 15 19.4477 15 20C15 20.5523 14.5523 21 14 21H9.02418C9.00802 21.0004 8.99181 21.0004 8.97557 21H5C4.44772 21 4 20.5523 4 20C4 19.4477 4.44772 19 5 19H8.30704L13.557 5H10C9.44772 5 9 4.55228 9 4C9 3.44772 9.44772 3 10 3H14.9782C14.9928 2.99968 15.0075 2.99967 15.0222 3Z" fill="currentColor"></path></svg>
				</button>
				
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Strike" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleStrike().run()" data-cmd="strike" aria-label="Strike" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9.00039 3H16.0001C16.5524 3 17.0001 3.44772 17.0001 4C17.0001 4.55229 16.5524 5 16.0001 5H9.00011C8.68006 4.99983 8.36412 5.07648 8.07983 5.22349C7.79555 5.37051 7.55069 5.5836 7.36585 5.84487C7.181 6.10614 7.06155 6.40796 7.01754 6.72497C6.97352 7.04198 7.00623 7.36492 7.11292 7.66667C7.29701 8.18737 7.02414 8.75872 6.50344 8.94281C5.98274 9.1269 5.4114 8.85403 5.2273 8.33333C5.01393 7.72984 4.94851 7.08396 5.03654 6.44994C5.12456 5.81592 5.36346 5.21229 5.73316 4.68974C6.10285 4.1672 6.59256 3.74101 7.16113 3.44698C7.72955 3.15303 8.36047 2.99975 9.00039 3Z" fill="currentColor"></path><path d="M18 13H20C20.5523 13 21 12.5523 21 12C21 11.4477 20.5523 11 20 11H4C3.44772 11 3 11.4477 3 12C3 12.5523 3.44772 13 4 13H14C14.7956 13 15.5587 13.3161 16.1213 13.8787C16.6839 14.4413 17 15.2044 17 16C17 16.7956 16.6839 17.5587 16.1213 18.1213C15.5587 18.6839 14.7956 19 14 19H6C5.44772 19 5 19.4477 5 20C5 20.5523 5.44772 21 6 21H14C15.3261 21 16.5979 20.4732 17.5355 19.5355C18.4732 18.5979 19 17.3261 19 16C19 14.9119 18.6453 13.8604 18 13Z" fill="currentColor"></path></svg>
				</button>
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Code" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleCode().run()" data-cmd="code" aria-label="Code" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M15.4545 4.2983C15.6192 3.77115 15.3254 3.21028 14.7983 3.04554C14.2712 2.88081 13.7103 3.1746 13.5455 3.70175L8.54554 19.7017C8.38081 20.2289 8.6746 20.7898 9.20175 20.9545C9.72889 21.1192 10.2898 20.8254 10.4545 20.2983L15.4545 4.2983Z" fill="currentColor"></path><path d="M6.70711 7.29289C7.09763 7.68342 7.09763 8.31658 6.70711 8.70711L3.41421 12L6.70711 15.2929C7.09763 15.6834 7.09763 16.3166 6.70711 16.7071C6.31658 17.0976 5.68342 17.0976 5.29289 16.7071L1.29289 12.7071C0.902369 12.3166 0.902369 11.6834 1.29289 11.2929L5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289Z" fill="currentColor"></path><path d="M17.2929 7.29289C17.6834 6.90237 18.3166 6.90237 18.7071 7.29289L22.7071 11.2929C23.0976 11.6834 23.0976 12.3166 22.7071 12.7071L18.7071 16.7071C18.3166 17.0976 17.6834 17.0976 17.2929 16.7071C16.9024 16.3166 16.9024 15.6834 17.2929 15.2929L20.5858 12L17.2929 8.70711C16.9024 8.31658 16.9024 7.68342 17.2929 7.29289Z" fill="currentColor"></path></svg>
				</button>
				<button class="tiptap-button" data-bs-toggle="tooltip" title="Underline" data-appearance="emphasized" data-tooltip-state="closed" data-style="ghost" type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleUnderline().run()" data-cmd="underline" aria-label="Underline" aria-pressed="false" tabindex="-1">
				   <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 4C7 3.44772 6.55228 3 6 3C5.44772 3 5 3.44772 5 4V10C5 11.8565 5.7375 13.637 7.05025 14.9497C8.36301 16.2625 10.1435 17 12 17C13.8565 17 15.637 16.2625 16.9497 14.9497C18.2625 13.637 19 11.8565 19 10V4C19 3.44772 18.5523 3 18 3C17.4477 3 17 3.44772 17 4V10C17 11.3261 16.4732 12.5979 15.5355 13.5355C14.5979 14.4732 13.3261 15 12 15C10.6739 15 9.40215 14.4732 8.46447 13.5355C7.52678 12.5979 7 11.3261 7 10V4ZM4 19C3.44772 19 3 19.4477 3 20C3 20.5523 3.44772 21 4 21H20C20.5523 21 21 20.5523 21 20C21 19.4477 20.5523 19 20 19H4Z" fill="currentColor"></path></svg>
				</button>';
	// Highlight
	$html .= '<div class="dropdown d-inline-block">
    <button class="tiptap-button dropdown-toggle hide-arrow"
            data-bs-toggle="dropdown"
            title="Highlight Color"
            data-style="ghost"
            type="button"
            id="highlight_' . $js_id . '"
            aria-expanded="false">
        <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.7072 4.70711C15.0977 4.31658 15.0977 3.68342 14.7072 3.29289C14.3167 2.90237 13.6835 2.90237 13.293 3.29289L8.69294 7.89286L8.68594 7.9C8.13626 8.46079 7.82837 9.21474 7.82837 10C7.82837 10.2306 7.85491 10.4584 7.90631 10.6795L2.29289 16.2929C2.10536 16.4804 2 16.7348 2 17V20C2 20.5523 2.44772 21 3 21H12C12.2652 21 12.5196 20.8946 12.7071 20.7071L15.3205 18.0937C15.5416 18.1452 15.7695 18.1717 16.0001 18.1717C16.7853 18.1717 17.5393 17.8639 18.1001 17.3142L22.7072 12.7071C23.0977 12.3166 23.0977 11.6834 22.7072 11.2929C22.3167 10.9024 21.6835 10.9024 21.293 11.2929L16.6971 15.8887C16.5105 16.0702 16.2605 16.1717 16.0001 16.1717C15.7397 16.1717 15.4897 16.0702 15.303 15.8887L10.1113 10.697C9.92992 10.5104 9.82837 10.2604 9.82837 10C9.82837 9.73963 9.92992 9.48958 10.1113 9.30297L14.7072 4.70711ZM13.5858 17L9.00004 12.4142L4 17.4142V19H11.5858L13.5858 17Z" />
        </svg>
    </button>
    <ul class="dropdown-menu shadow-sm" aria-labelledby="highlight_' . $js_id . '" style="min-width:auto;padding:8px 12px;border-radius: 9999px;">
        <div class="d-flex flex-wrap" style="width: 200px;gap:5px;">
            <button type="button" data-bs-toggle="tooltip" title="Green background" class="color-swatch" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHighlight({ color: \'#599A6F\' }).run()">
                <span style="background-color:#599A6F;"></span>
			</button>
            <button type="button" data-bs-toggle="tooltip" title="Blue background" class="color-swatch" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHighlight({ color: \'#7597AE\' }).run()">
            	<span style="background-color:#7597AE;"></span>
			</button>
            <button type="button" data-bs-toggle="tooltip" title="Red background"  class="color-swatch" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHighlight({ color: \'#7B484B\' }).run()">
            	<span style="background-color:#7B484B;"></span>
			</button>
            <button type="button" data-bs-toggle="tooltip" title="Purple background" class="color-swatch" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHighlight({ color: \'#60487B\' }).run()">
            	<span style="background-color:#60487B;"></span>
			</button>
            <button type="button" data-bs-toggle="tooltip" title="Yellow background" class="color-swatch" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHighlight({ color: \'#726D2F\' }).run()">
            	<span style="background-color:#726D2F;"></span>
			</button>
			<div class="tiptap-separator" data-orientation="vertical" aria-orientation="vertical" role="separator"></div>
            <button type="button" class="color-swatch" style="color:rgba(247, 247, 253, 0.64);width:28px;height:28px;" onclick="window.editors[\'' . $js_id . '\'].chain().focus().unsetHighlight().run()">
                <svg width="16" height="16" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.43471 4.01458C4.34773 4.06032 4.26607 4.11977 4.19292 4.19292C4.11977 4.26607 4.06032 4.34773 4.01458 4.43471C2.14611 6.40628 1 9.0693 1 12C1 18.0751 5.92487 23 12 23C14.9306 23 17.5936 21.854 19.5651 19.9856C19.6522 19.9398 19.7339 19.8803 19.8071 19.8071C19.8803 19.7339 19.9398 19.6522 19.9856 19.5651C21.854 17.5936 23 14.9306 23 12C23 5.92487 18.0751 1 12 1C9.0693 1 6.40628 2.14611 4.43471 4.01458ZM6.38231 4.9681C7.92199 3.73647 9.87499 3 12 3C16.9706 3 21 7.02944 21 12C21 14.125 20.2635 16.078 19.0319 17.6177L6.38231 4.9681ZM17.6177 19.0319C16.078 20.2635 14.125 21 12 21C7.02944 21 3 16.9706 3 12C3 9.87499 3.73647 7.92199 4.9681 6.38231L17.6177 19.0319Z" fill="currentColor"></path></svg>
			</button>
        </div>
    </ul>
	</div>';
	
	if (!defined('XY')) {
		define('XY', TRUE);
		?>
		<script>
            // Create a helper function to avoid repeating code
            // Define the sync logic once to keep it clean
            const syncUI = ({ editor }) => {
                const currentWrapper = document.querySelector('#wrapper_' + id);
                if (!currentWrapper) return;

                currentWrapper.querySelectorAll('button[data-cmd]').forEach(btn => {
                    const cmd = btn.getAttribute('data-cmd');
                    const level = btn.getAttribute('data-level');

                    const isActive = level
                        ? editor.isActive(cmd, { level: parseInt(level) })
                        : editor.isActive(cmd);

                    btn.classList.toggle('is-active', isActive);
                    btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    btn.setAttribute('data-active-state', isActive ? 'on' : 'off');
                });
            };
            /**
             * Simple Link Opener
             * Directly opens whatever is in the URL input field.
             */
            window.openTiptapLinkPreview = function (js_id) {
                const urlInput = document.getElementById('link_url_' + js_id);
                const url = urlInput ? urlInput.value.trim() : '';

                if (url) {
                    // Just prefix with https:// and open, as requested
                    window.open('https://' + url.replace(/^https?:\/\//, ''), '_blank');
                }
            };
            /**
             * Removes the link from the editor and resets the popup UI
             */
            /**
             * Safely removes a link from Tiptap and resets the popup UI.
             */
            window.removeTiptapLink = function (js_id) {
                const editor = window.editors[js_id];
                if (!editor) return;

                // 1. Remove link from editor
                editor.chain().focus().unsetLink().run();

                // 2. Reset the URL input
                const urlInput = document.getElementById('link_url_' + js_id);
                if (urlInput) urlInput.value = '';

                // 3. Safely reset the Target Toggle Button
                const targetBtn = document.getElementById('link_target_btn_' + js_id);
                if (targetBtn) {
                    targetBtn.dataset.target = '_self';
                    targetBtn.classList.remove('bg-secondary', 'text-white');
                }

                // 4. Close the dropdown (Bootstrap 5)
                const triggerEl = document.getElementById('link_btn_' + js_id);
                if (triggerEl) {
                    const instance = bootstrap.Dropdown.getOrCreateInstance(triggerEl);
                    if (instance) instance.hide();
                }
            };

            /**
             * Smart Link Handler for Tiptap
             * Handles both wrapping selection and inserting new links at cursor
             */
            window.applyTiptapLink = function (js_id) {
                const editor = window.editors[js_id];
                const urlInput = document.getElementById('link_url_' + js_id);
                const url = urlInput ? urlInput.value.trim() : '';
                const targetBtn = document.getElementById('link_target_btn_' + js_id);
                const targetValue = (targetBtn && targetBtn.dataset.target === '_blank') ? '_blank' : '';

                if (!editor || !url) return;

                if (editor.state.selection.empty) {
                    // Insert new link at cursor
                    editor.chain()
                        .focus()
                        .insertContent(`<a href="${url}" target="${targetValue}">${url}</a> `)
                        .run();
                } else {
                    // Wrap existing selection
                    editor.chain()
                        .focus()
                        .extendMarkRange('link')
                        .setLink({href: url, target: targetValue})
                        .run();
                }

                // Close Bootstrap Dropdown
                const triggerEl = document.getElementById('link_btn_' + js_id);
                if (triggerEl) {
                    const instance = bootstrap.Dropdown.getOrCreateInstance(triggerEl);
                    if (instance) instance.hide();
                }
            };
            /**
             * Prepares the Link Popup UI by reading current editor attributes
             */
            window.prepareTiptapLinkPopup = function (js_id) {
                const editor = window.editors[js_id];
                if (!editor) return;

                const attrs = editor.getAttributes('link');

                // 1. Fill the URL input
                const urlInput = document.getElementById('link_url_' + js_id);
                if (urlInput) {
                    urlInput.value = attrs.href || '';
                }

                // 2. Set the "New Window" toggle state
                const targetBtn = document.getElementById('link_target_btn_' + js_id);
                if (targetBtn) {
                    if (attrs.target === '_blank') {
                        targetBtn.dataset.target = '_blank';
                        targetBtn.classList.add('bg-secondary', 'text-white');
                        targetBtn.classList.remove('btn-light');
                    } else {
                        targetBtn.dataset.target = '_self';
                        targetBtn.classList.remove('bg-secondary', 'text-white');
                        targetBtn.classList.add('btn-light');
                    }
                }
            };
		</script>
		<?php
	}
	
	//Links
	$html .= '<div class="dropdown d-inline-block">';
	$html .= '<button class="tiptap-button dropdown-toggle hide-arrow"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        title="Link"
        data-cmd="link"
        data-style="ghost"
        type="button"
        id="link_btn_' . $js_id . '"
        onclick="prepareTiptapLinkPopup(\'' . $js_id . '\')">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tiptap-button-icon">
				<path d="M10 14a3.5 3.5 0 0 0 5 0l4 -4a3.5 3.5 0 0 0 -5 -5l-.5 .5" />
				<path d="M14 10a3.5 3.5 0 0 0 -5 0l-4 4a3.5 3.5 0 0 0 5 5l.5 -.5" />
			</svg>
		</button>';
	
	$html .= '<div class="dropdown-menu shadow-sm p-1" aria-labelledby="link_btn_' . $js_id . '" style="min-width: 320px; z-index: 1050; border-radius: 8px;">
        <div class="d-flex align-items-center" style="gap: 4px;">
            <input type="url" id="link_url_' . $js_id . '" class="form-control form-control-sm border-0 shadow-none" placeholder="Paste a link..." autocomplete="off" style="background: transparent; min-width: 160px; outline: none;">
            ';
	// Apply
	$html .= '<button type="button"
					class="btn btn-sm btn-light p-0 d-inline-flex align-items-center justify-content-center"
					title="Apply link"
					style="width: 28px; height: 28px; min-width: 28px; flex-shrink: 0; border: none;"
					onclick="applyTiptapLink(\'' . $js_id . '\')">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="9 10 4 15 9 20"></polyline>
					<path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
				</svg>
			</button>';
	// Seperator
	$html .= '<div class="vr" style="height: 20px; align-self: center; opacity: 0.15;"></div>';
	
	$html .= '<button type="button"
					id="link_open_btn_' . $js_id . '"
					class="btn btn-sm btn-light p-0 d-inline-flex align-items-center justify-content-center"
					title="Open link"
					style="width: 28px; height: 28px; min-width: 28px; max-width: 28px; border: none; flex-shrink: 0;"
					onclick="openTiptapLinkPreview(\'' . $js_id . '\')">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
					<polyline points="15 3 21 3 21 9"></polyline>
					<line x1="10" y1="14" x2="21" y2="3"></line>
				</svg>
			</button>';
	
	$html .= '<button type="button"
				class="btn btn-sm btn-light text-danger p-0 d-inline-flex align-items-center justify-content-center"
				title="Remove link"
				style="width: 28px; height: 28px; min-width: 28px; flex-shrink: 0; border: none;"
				onclick="removeTiptapLink(\'' . $js_id . '\')">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M7 5V4C7 3.17477 7.40255 2.43324 7.91789 1.91789C8.43324 1.40255 9.17477 1 10 1H14C14.8252 1 15.5668 1.40255 16.0821 1.91789C16.5975 2.43324 17 3.17477 17 4V5H21C21.5523 5 22 5.44772 22 6C22 6.55228 21.5523 7 21 7H20V20C20 20.8252 19.5975 21.5668 19.0821 22.0821C18.5668 22.5975 17.8252 23 17 23H7C6.17477 23 5.43324 22.5975 4.91789 22.0821C4.40255 21.5668 4 20.8252 4 20V7H3C2.44772 7 2 6.55228 2 6C2 5.44772 2.44772 5 3 5H7ZM9 4C9 3.82523 9.09745 3.56676 9.33211 3.33211C9.56676 3.09745 9.82523 3 10 3H14C14.1748 3 14.4332 3.09745 14.6679 3.33211C14.9025 3.56676 15 3.82523 15 4V5H9V4ZM6 7V20C6 20.1748 6.09745 20.4332 6.33211 20.6679C6.56676 20.9025 6.82523 21 7 21H17C17.1748 21 17.4332 20.9025 17.6679 20.6679C17.9025 20.4332 18 20.1748 18 20V7H6Z"></path>
			</svg>
		</button>';
	
	$html .= '</div>
    </div>
</div>';
	
	$html .= '<div class="tiptap-separator" data-orientation="vertical" role="none"></div>';
	// Superscript
	$html .= '<div role="group" class="tiptap-toolbar-group d-flex gap-1">';
	$html .= '<button type="button"
        class="tiptap-button"
        data-bs-toggle="tooltip"
        title="Superscript"
        data-style="ghost"
        onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleSuperscript().run()"
        aria-label="Superscript">
		<svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M12.7071 7.29289C13.0976 7.68342 13.0976 8.31658 12.7071 8.70711L4.70711 16.7071C4.31658 17.0976 3.68342 17.0976 3.29289 16.7071C2.90237 16.3166 2.90237 15.6834 3.29289 15.2929L11.2929 7.29289C11.6834 6.90237 12.3166 6.90237 12.7071 7.29289Z" fill="currentColor"></path>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M3.29289 7.29289C3.68342 6.90237 4.31658 6.90237 4.70711 7.29289L12.7071 15.2929C13.0976 15.6834 13.0976 16.3166 12.7071 16.7071C12.3166 17.0976 11.6834 17.0976 11.2929 16.7071L3.29289 8.70711C2.90237 8.31658 2.90237 7.68342 3.29289 7.29289Z" fill="currentColor"></path>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M17.405 1.40657C18.0246 1.05456 18.7463 0.92634 19.4492 1.04344C20.1521 1.16054 20.7933 1.51583 21.2652 2.0497L21.2697 2.05469L21.2696 2.05471C21.7431 2.5975 22 3.28922 22 4.00203C22 5.08579 21.3952 5.84326 20.7727 6.34289C20.1966 6.80531 19.4941 7.13675 18.9941 7.37261C18.9714 7.38332 18.9491 7.39383 18.9273 7.40415C18.4487 7.63034 18.2814 7.78152 18.1927 7.91844C18.1778 7.94155 18.1625 7.96834 18.1473 8.00003H21C21.5523 8.00003 22 8.44774 22 9.00003C22 9.55231 21.5523 10 21 10H17C16.4477 10 16 9.55231 16 9.00003C16 8.17007 16.1183 7.44255 16.5138 6.83161C16.9107 6.21854 17.4934 5.86971 18.0728 5.59591C18.6281 5.33347 19.1376 5.09075 19.5208 4.78316C19.8838 4.49179 20 4.25026 20 4.00203C20 3.77192 19.9178 3.54865 19.7646 3.37182C19.5968 3.18324 19.3696 3.05774 19.1205 3.01625C18.8705 2.97459 18.6137 3.02017 18.3933 3.14533C18.1762 3.26898 18.0191 3.45826 17.9406 3.67557C17.7531 4.19504 17.18 4.46414 16.6605 4.27662C16.141 4.0891 15.8719 3.51596 16.0594 2.99649C16.303 2.3219 16.7817 1.76125 17.4045 1.40689L17.405 1.40657Z" fill="currentColor"></path>
		</svg>
	</button>';
	$html .= '<button type="button"
			class="tiptap-button"
			data-bs-toggle="tooltip"
			title="Subscript"
			data-style="ghost"
			onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleSubscript().run()"
			aria-label="Subscript">
		<svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M3.29289 7.29289C3.68342 6.90237 4.31658 6.90237 4.70711 7.29289L12.7071 15.2929C13.0976 15.6834 13.0976 16.3166 12.7071 16.7071C12.3166 17.0976 11.6834 17.0976 11.2929 16.7071L3.29289 8.70711C2.90237 8.31658 2.90237 7.68342 3.29289 7.29289Z" fill="currentColor"></path>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M12.7071 7.29289C13.0976 7.68342 13.0976 8.31658 12.7071 8.70711L4.70711 16.7071C4.31658 17.0976 3.68342 17.0976 3.29289 16.7071C2.90237 16.3166 2.90237 15.6834 3.29289 15.2929L11.2929 7.29289C11.6834 6.90237 12.3166 6.90237 12.7071 7.29289Z" fill="currentColor"></path>
			<path fill-rule="evenodd" clip-rule="evenodd" d="M17.4079 14.3995C18.0284 14.0487 18.7506 13.9217 19.4536 14.0397C20.1566 14.1578 20.7933 14.5138 21.2696 15.0481L21.2779 15.0574L21.2778 15.0575C21.7439 15.5988 22 16.2903 22 17C22 18.0823 21.3962 18.8401 20.7744 19.3404C20.194 19.8073 19.4858 20.141 18.9828 20.378C18.9638 20.387 18.9451 20.3958 18.9266 20.4045C18.4473 20.6306 18.2804 20.7817 18.1922 20.918C18.1773 20.9412 18.1619 20.9681 18.1467 21H21C21.5523 21 22 21.4477 22 22C22 22.5523 21.5523 23 21 23H17C16.4477 23 16 22.5523 16 22C16 21.1708 16.1176 20.4431 16.5128 19.832C16.9096 19.2184 17.4928 18.8695 18.0734 18.5956C18.6279 18.334 19.138 18.0901 19.5207 17.7821C19.8838 17.49 20 17.2477 20 17C20 16.7718 19.9176 16.5452 19.7663 16.3672C19.5983 16.1792 19.3712 16.0539 19.1224 16.0121C18.8722 15.9701 18.6152 16.015 18.3942 16.1394C18.1794 16.2628 18.0205 16.4549 17.9422 16.675C17.7572 17.1954 17.1854 17.4673 16.665 17.2822C16.1446 17.0972 15.8728 16.5254 16.0578 16.005C16.2993 15.3259 16.7797 14.7584 17.4039 14.4018L17.4079 14.3995L17.4079 14.3995Z" fill="currentColor"></path>
		</svg>
	</button>';
	$html .= '</div>'; // ends buttongroup
	$html .= '<div class="tiptap-separator" data-orientation="vertical" role="none"></div>';
	$html .= '<div role="group" class="tiptap-toolbar-group d-flex gap-1">';
	
	// Align Left
	$html .= '<button type="button" class="tiptap-button" data-bs-toggle="tooltip" title="Align left"
                onclick="window.editors[\'' . $js_id . '\'].chain().focus().setTextAlign(\'left\').run()">
        <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 5.44772 2.44772 5 3 5H21C21.5523 5 22 5.44772 22 6C22 6.55228 21.5523 7 21 7H3C2.44772 7 2 6.55228 2 6Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2 12C2 11.4477 2.44772 11 3 11H15C15.5523 11 16 11.4477 16 12C16 12.5523 15.5523 13 15 13H3C2.44772 13 2 12.5523 2 12Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2 18C2 17.4477 2.44772 17 3 17H17C17.5523 17 18 17.4477 18 18C18 18.5523 17.5523 19 17 19H3C2.44772 19 2 18.5523 2 18Z"/></svg>
    </button>';
	
	// Align Center
	$html .= '<button type="button" class="tiptap-button" data-bs-toggle="tooltip" title="Align center"
                onclick="window.editors[\'' . $js_id . '\'].chain().focus().setTextAlign(\'center\').run()">
        <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 5.44772 2.44772 5 3 5H21C21.5523 5 22 5.44772 22 6C22 6.55228 21.5523 7 21 7H3C2.44772 7 2 6.55228 2 6Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6 12C6 11.4477 6.44772 11 7 11H17C17.5523 11 18 11.4477 18 12C18 12.5523 17.5523 13 17 13H7C6.44772 13 6 12.5523 6 12Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M4 18C4 17.4477 4.44772 17 5 17H19C19.5523 17 20 17.4477 20 18C20 18.5523 19.5523 19 19 19H5C4.44772 19 4 18.5523 4 18Z"/></svg>
    </button>';
	
	// Align Right
	$html .= '<button type="button" class="tiptap-button" data-bs-toggle="tooltip" title="Align right"
                onclick="window.editors[\'' . $js_id . '\'].chain().focus().setTextAlign(\'right\').run()">
        <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 5.44772 2.44772 5 3 5H21C21.5523 5 22 5.44772 22 6C22 6.55228 21.5523 7 21 7H3C2.44772 7 2 6.55228 2 6Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8 12C8 11.4477 8.44772 11 9 11H21C21.5523 11 22 11.4477 22 12C22 12.5523 21.5523 13 21 13H9C8.44772 13 8 12.5523 8 12Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6 18C6 17.4477 6.44772 17 7 17H21C21.5523 17 22 17.4477 22 18C22 18.5523 21.5523 19 21 19H7C6.44772 19 6 18.5523 6 18Z"/></svg>
    </button>';
	
	// Align Justify
	$html .= '<button type="button" class="tiptap-button" data-bs-toggle="tooltip" title="Align justify"
                onclick="window.editors[\'' . $js_id . '\'].chain().focus().setTextAlign(\'justify\').run()">
        <svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 5.44772 2.44772 5 3 5H21C21.5523 5 22 5.44772 22 6C22 6.55228 21.5523 7 21 7H3C2.44772 7 2 6.55228 2 6Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2 12C2 11.4477 2.44772 11 3 11H21C21.5523 11 22 11.4477 22 12C22 12.5523 21.5523 13 21 13H3C2.44772 13 2 12.5523 2 12Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2 18C2 17.4477 2.44772 17 3 17H21C21.5523 17 22 17.4477 22 18C22 18.5523 21.5523 19 21 19H3C2.44772 19 2 18.5523 2 18Z"/></svg>
    </button>';
	
	$html .= '</div>';


//		$html .= '<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().unsetAllMarks().run()">Clear marks</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().clearNodes().run()">Clear nodes</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().setParagraph().run()" data-cmd="paragraph">Paragraph</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 1 }).run()" data-cmd="heading" data-level="1">H1</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 2 }).run()" data-cmd="heading" data-level="2">H2</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 3 }).run()" data-cmd="heading" data-level="3">H3</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 4 }).run()" data-cmd="heading" data-level="4">H4</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 5 }).run()" data-cmd="heading" data-level="5">H5</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleHeading({ level: 6 }).run()" data-cmd="heading" data-level="6">H6</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleBulletList().run()" data-cmd="bulletList">Bullet list</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleOrderedList().run()" data-cmd="orderedList">Ordered list</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleCodeBlock().run()" data-cmd="codeBlock">Code block</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().toggleBlockquote().run()" data-cmd="blockquote">Blockquote</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().setHorizontalRule().run()">Horizontal rule</button>
//				<button type="button" onclick="window.editors[\'' . $js_id . '\'].chain().focus().setHardBreak().run()">Hard break</button>
//				';
	$html .= '</div>
		</div>
		<div id="editor_' . $js_id . '" class="tiptap-editor"></div>
	</div>
	';
	
	// 3. The JS Logic
	add_to_footer("
	<script>
		(function() {
			const id = '" . $js_id . "';
		
			const init_" . $js_id . " = () => {
		// 1. DEFINE WRAPPER HERE
		  const wrapper = document.querySelector('#wrapper_' + id);
		  if (!wrapper) return false;
		
			const el = document.querySelector('#editor_' + id);
			// Ensure the element exists before trying to mount Tiptap
			if (!el) return false;
	
			// Wait for the Tiptap engine to be globally available
			if (typeof window.TiptapEditor === 'undefined') {
				if (window.attempts % 10 === 0) console.log('Tiptap: Waiting for engine for ' + id);
				return false;
			}
	
			const bubbleMenuEl = document.querySelector('#wrapper_' + id + ' > .tiptap-bubble-menu');
			if (!bubbleMenuEl) {
				console.error('Tiptap: Bubble Menu element not found for ' + id);
				return false;
			}

			// Check if the positioning engine exists
			if (typeof tippy === 'undefined') {
				console.warn('Tiptap: Tippy.js is missing! BubbleMenu will not work.');
			}

			// Define the sync logic inside here so it's locked to this specific 'id'
			// Ensure syncUI accepts the editor directly
			const syncUI = (editorInstance) => {
            if (!bubbleMenuEl || !editorInstance) return;

            bubbleMenuEl.querySelectorAll('button[data-cmd]').forEach(btn => {
                const cmd = btn.getAttribute('data-cmd');
                const level = btn.getAttribute('data-level');

                const isActive = level
                    ? editorInstance.isActive(cmd, { level: parseInt(level) })
                    : editorInstance.isActive(cmd);

                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('data-active-state', isActive ? 'on' : 'off');
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            	});
        	};
			// 2. STAGE TWO: Check if the EXTENSIONS are here
			// We check for TiptapSubscript because it's near the end of your full.js file.
			// If Subscript is there, Highlight is definitely there too.
			const coreExts = [
			   'TiptapEditor',
			   'TiptapStarterKit',
			   'TiptapHighlight',
			   'TiptapSuperscript',
			   'TiptapSubscript',
			   'TiptapBubbleMenu', // Add this to your check!
			];

			const allExtsReady = coreExts.every(ext => typeof window[ext] !== 'undefined');

			if (!allExtsReady) {
			   // This will now correctly print 'Nope' until the WHOLE file is loaded
			   if (window.attempts % 10 === 0) console.log('Tiptap: Still waiting for full extension bundle...');
			   return false;
			}
			
			try {
				window.editors = window.editors || {};
				// Prevent double-initialization
				if (window.editors[id]) return true;
	
				const initialContent = atob('" . $b64_content . "');
	
				window.editors[id] = new window.TiptapEditor({
					element: el,
					extensions: [
						window.TiptapStarterKit,
						window.TiptapBubbleMenu.configure({
							element: bubbleMenuEl, // Scoped to this specific textarea
							tippyOptions: {
								duration: 100,
								// 'parent' attaches it to the wrapper so it stays
								// with its specific editor instance
								appendTo: wrapper,
								interactive: true,
								// Ensure it doesn't get clipped if the wrapper has overflow:hidden
								boundary: wrapper,
							},
						}),
						window.TiptapHighlight.configure({
         						multicolor: true
      					}),
               			window.TiptapSuperscript,
               			window.TiptapSubscript,
               			window.TiptapTextAlign.configure({
						  types: ['heading', 'paragraph'], // Allow alignment for these tags
						  alignments: ['left', 'center', 'right', 'justify'], // The options you want
						  defaultAlignment: 'left',
						}),
					],
					content: initialContent,
					editorProps: {
						attributes: {
							class: 'tiptap " . ($options['inner_class'] ?? '') . "',
							style: 'min-height: " . ($options['height'] ?? '200px') . "; outline: none;'
						}
					},
					// 1. Triggered when clicking/highlighting (fixes your current issue)
					onSelectionUpdate({ editor }) {
						syncUI(editor);
					},
					onTransaction({ editor }) { syncUI(editor); },
					onUpdate({ editor }) {
						const target = document.getElementById(id);
						if (target) target.value = editor.getHTML();
						syncUI(editor);
					}
				});
	
				// Initialize Bootstrap 5 Tooltips for this specific toolbar
				const toolWrapper = document.querySelector('#wrapper_' + id);
				if (toolWrapper && typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
					const tooltipTriggerList = [].slice.call(toolWrapper.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));
					tooltipTriggerList.map(function (tooltipTriggerEl) {
						return new bootstrap.Tooltip(tooltipTriggerEl, {
							trigger: 'hover',
							// 500ms delay matches the 'official' sleek feel
							delay: { \"show\": 500, \"hide\": 100 },
							container: 'body'
						});
					});
				}
	
				console.log('Tiptap: ' + id + ' initialized successfully.');
				return true;
			} catch (err) {
				console.error('Tiptap Crash: ' + id, err);
				return true;
			}
		};
	
		window.attempts = 0;
		const poller = setInterval(() => {
			window.attempts++;
			// Stop polling if initialized or after 50 attempts (10 seconds)
			if (init_" . $js_id . "() || window.attempts > 50) {
				clearInterval(poller);
				if (window.attempts > 50) console.error('Tiptap: Timeout loading engine for ' + id);
			}
		}, 200);
	})();
	</script>
	");
	
	
	return $html;
}
