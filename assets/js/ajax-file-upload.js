var WPFM_AjaxFileUpload = function () {
	/// <summary>Constructor function of the food WPFM_AjaxFileUpload class.</summary>
	/// <returns type="Home" />      
	return {
		/// <summary>
		/// Initializes the ajax file upload.  
		/// </summary>     
		/// <returns type="initialization settings" />   
		/// <since>1.0.0</since>
		init: function () {
			jQuery('.wp-food-manager-file-upload').each(function () {			
				jQuery(this).fileupload({
					dataType: 'json',
					dropZone: jQuery(this),
					url: wpfm_ajax_file_upload.ajax_url.toString().replace("%%endpoint%%", "upload_file"),
					maxNumberOfFiles: 1,
					formData: {
						script: true
					},
					add: function (e, data) {
						var $file_field = jQuery(this);
						var $form = $file_field.closest('form');
						var $uploaded_files = $file_field.parent().find('.food-manager-uploaded-files');
						var uploadErrors = [];
						// Validate type.
						var allowed_types = jQuery(this).data('file_types');
						if (allowed_types) {
							var acceptFileTypes = new RegExp("(\.|\/)(" + allowed_types + ")$", "i");
							if (data.originalFiles[0]['name'].length && !acceptFileTypes.test(data.originalFiles[0]['name'])) {
								uploadErrors.push(wpfm_ajax_file_upload.i18n_invalid_file_type);
							}
						}
						if (uploadErrors.length > 0) {
							alert(uploadErrors.join("\n"));
						} else {
							$form.find(':input[type="submit"]').attr('disabled', 'disabled');
							data.context = jQuery('<progress value="" max="100"></progress>').appendTo($uploaded_files);
							data.submit();
						}
					},
					progress: function (e, data) {
						var $file_field = jQuery(this);
						var $uploaded_files = $file_field.parent().find('.food-manager-uploaded-files');
						var progress = parseInt(data.loaded / data.total * 100, 10);
						data.context.val(progress);
					},
					fail: function (e, data) {
						var $file_field = jQuery(this);
						var $form = $file_field.closest('form');
						$form.find(':input[type="submit"]').removeAttr('disabled');
					},
					done: function (e, data) {
						var $file_field = jQuery(this);
						var $form = $file_field.closest('form');
						var $uploaded_files = $file_field.parent().find('.food-manager-uploaded-files');
						var multiple = $file_field.attr('multiple') ? 1 : 0;
						var image_types = ['jpg', 'gif', 'png', 'jpeg', 'jpe', 'webp'];
						data.context.remove();
						jQuery.each(data.result.files, function (index, file) {
							if (file.error) {
								alert(file.error);
							} else {
								if (jQuery.inArray(file.extension, image_types) >= 0) {
									var html = jQuery.parseHTML(wpfm_ajax_file_upload.js_field_html_img);
									jQuery(html).find('.food-manager-uploaded-file-preview img').attr('src', file.url);
								} else {
									var html = jQuery.parseHTML(wpfm_ajax_file_upload.js_field_html);
									jQuery(html).find('.food-manager-uploaded-file-name code').text(file.name);
								}
								jQuery(html).find('.input-text').val(file.url);
								jQuery(html).find('.input-text').attr('name', $file_field.attr('name'));
								if (multiple) {
									$uploaded_files.append(html);
								} else {
									$uploaded_files.html(html);
								}
							}
						});
						$form.find(':input[type="submit"]').removeAttr('disabled');
					}
				});
			});
		}
	} //enf of return.
}; //end of class.

WPFM_AjaxFileUpload = WPFM_AjaxFileUpload();
jQuery(document).ready(function ($) {
	WPFM_AjaxFileUpload.init();
});