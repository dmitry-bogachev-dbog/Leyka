<?php if( !defined('WPINC') ) die;

// Sections:
add_action('leyka_render_section', 'leyka_render_section_area');
function leyka_render_section_area($section){?>

    <div class="leyka-options-section <?php echo $section['is_default_collapsed'] ? 'collapsed' : '';?> <?php echo !empty($section['tabs']) ? 'with-tabs' : '';?>" id="<?php echo $section['name'];?>">
        <div class="header"><h3><?php echo esc_attr($section['title']);?></h3></div>
        <div class="content">

            <?php if( !empty($section['description']) ) {?>
                <div class="section-description"><?php echo $section['description'];?></div>
            <?php }

            if( !empty($section['content_area_render']) && function_exists($section['content_area_render']) ) {
                call_user_func($section['content_area_render'], $section);
            } else {
                foreach($section['options'] as $option) {
    
                    $option_info = leyka_options()->get_info_of($option);
                    do_action("leyka_render_{$option_info['type']}", $option, $option_info);
    
                }
            }

            if( !empty($section['is_separate_sections_forms']) ) { ?>
                <p class="submit">
                    <input type="submit" name="leyka_settings_<?php echo $section['current_stage'];?>_submit" class="button-primary" <?php if(!empty($section['action_button']['id'])) { printf(' id="%s" ', $section['action_button']['id']); } ?>" value="<?php echo !empty($section['action_button']['title']) ? $section['action_button']['title'] : __('Save', 'leyka');?>">
                </p>
            <?php }?>

        </div>
    </div>

<?php }

// Text fields:
add_action('leyka_render_text', 'leyka_render_text_field', 10, 2);
function leyka_render_text_field($option_id, $data){
    
    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-text-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo (empty($data['required']) ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>"  class="<?php echo !empty($data['mask']) ?  'leyka-wizard-mask' : '';?>">
            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// Email text fields:
add_action('leyka_render_email', 'leyka_render_email_field', 10, 2);
function leyka_render_email_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-text-field-wrapper leyka-email-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo (empty($data['required']) ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <span class="field-component field">
                <input type="<?php echo empty($data['is_password']) ? 'text' : 'password';?>" <?php echo !empty($data['mask']) ?  'data-inputmask="'.$data['mask'].'"' : '';?> id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" maxlength="<?php echo empty($data['length']) ? '' : (int)$data['length'];?>"  class="<?php echo !empty($data['mask']) ?  'leyka-wizard-mask' : '';?>">
            </span>

            <?php if( !empty($data['description']) ) {?>
                <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// File fields:
add_action('leyka_render_file', 'leyka_render_file_field', 10, 2);
function leyka_render_file_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $file_data = $data['value'] ? wp_get_attachment_metadata($data['value']) : array();?>

    <div class="leyka-file-field-wrapper <?php echo $option_id;?>-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>" id="<?php echo $option_id;?>-upload">

        <?php if( !empty($data['title']) ) {?>
        <span class="field-component title">

            <?php echo $data['title'];

            if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
            <?php }?>

        </span>
        <?php }?>

        <div class="preview-wrapper">

            <div class="uploaded-file-preview" <?php echo $data['value'] ? '' : 'style="display: none;"';?>>

                <?php _e('Uploaded:', 'leyka');?>

                <span class="file-preview">
                <?php $upload_dir = wp_upload_dir();

                if( $data['value'] && file_exists($upload_dir['basedir'].'/'.ltrim($data['value'], '/')) ) {?>
                    <img src="<?php echo $upload_dir['baseurl'].'/'.ltrim($data['value'], '/');?>" alt="" class="leyka-upload-image-preview">
                <?php } else if($file_data && !empty($file_data['file'])) {
                    echo wp_basename($file_data['file']);
                }?>
                </span>

                <a href="#" class="delete-uploaded-file" title="<?php _e('Delete the uploaded file');?>"></a>

            </div>
            <div class="loading-indicator-wrap" style="display: none;">
                <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
            </div>

        </div>

        <div class="upload-field field-wrapper flex" data-upload-title="<?php echo empty($data['upload_title']) ? __('Select a file', 'leyka') : $data['upload_title'];?>" data-option-id="<?php echo $option_id;?>">

            <span class="field-component field">
                <?php $field_id = $option_id.'-upload-button-'.wp_rand();?>
                <input type="file" value="" id="<?php echo $field_id;?>" <?php // echo empty($data['is_multiple']) ? '' : 'multiple';?> data-nonce="<?php echo wp_create_nonce('leyka-upload-'.$option_id);?>">
            </span>

            <label for="<?php echo $field_id;?>" class="field-component label upload-picture" id="<?php echo $option_id;?>-upload-button">
                <?php echo empty($data['upload_label']) ? __('Upload', 'leyka') : $data['upload_label'];?>
            </label>

        <?php if( !empty($data['description']) ) {?>
            <span class="field-component help">
                <label for="<?php echo $field_id;?>"><?php echo $data['description'];?></label>
            </span>
        <?php }?>

            <input type="hidden" id="leyka-upload-<?php echo $option_id;?>" name="<?php echo $option_id;?>" value="<?php echo $data['value'];?>">

        </div>

    </div>

<?php }

// Legend fields:
add_action('leyka_render_legend', 'leyka_render_legend_field', 10, 2);
function leyka_render_legend_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-legend-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">
            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>
            <span class="field-component field">
                <?php echo $data['text'];?>
            </span>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>
        </label>
    </div>

<?php }

// Number fields:
add_action('leyka_render_number', 'leyka_render_number_field', 10, 2);
function leyka_render_number_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-number-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">
                <input type="number" id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>" placeholder="<?php echo empty($data['placeholder']) ? '' : esc_attr($data['placeholder']);?>" <?php echo empty($data['length']) ? '' : 'maxlength="'.(int)$data['length'].'"';?> <?php echo isset($data['max']) ? 'max="'.(int)$data['max'].'"' : '';?> <?php echo isset($data['min']) ? 'min="'.(int)$data['min'].'"' : '';?> <?php echo empty($data['step']) ? '' : 'step="'.(int)$data['step'].'"';?>>
            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>
    </div>

<?php }

// Checkbox fields:
add_action('leyka_render_checkbox', 'leyka_render_checkbox_field', 10, 2);
function leyka_render_checkbox_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-checkbox-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">

            <?php if(empty($data['short_format'])) {?>
            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>

                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>
            <?php }?>

            <span class="field-component field">

                <input type="checkbox" id="<?php echo esc_attr($option_id.'-field');?>" name="<?php echo esc_attr($option_id);?>" value="1" <?php echo !empty($data['value']) && (int)$data['value'] >= 1 ? 'checked' : '';?>>&nbsp;

            <?php if( !empty($data['short_format']) ) {

                echo $data['title'];

                if( !empty($data['comment'])) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }

            } else {
                echo empty($data['description']) ? '' : $data['description'];
            }?>
            </span>

        </label>
    </div>

<?php }

// Multicheckbox fields:
add_action('leyka_render_multi_checkbox', 'leyka_render_multi_checkboxes_fields', 10, 2);
function leyka_render_multi_checkboxes_fields($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id; ?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-multi-checkboxes-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <span class="field-component title">
            <span class="text"><?php echo $data['title'];?></span>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
            <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
            <?php }?>
        </span>

        <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $label) {?>
                <label for="<?php echo $option_id.'-'.$value.'-field';?>">
                    <input type="checkbox" id="<?php echo $option_id.'-'.$value.'-field';?>" name="<?php echo $option_id;?>[]" value="<?php echo $value;?>" <?php echo in_array($value, $data['value']) ? 'checked' : '';?>>&nbsp;
                    <?php echo esc_attr($label);?>
                </label>                
            <?php }?>
        </span>

    </div>

<?php }

// Radio fields:
add_action('leyka_render_radio', 'leyka_render_radio_fields', 10, 2);
function leyka_render_radio_fields($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-radio-field-wrapper field-radio <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <span class="field-component title">
            <span class="text"><?php echo $data['title'];?></span>
            <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
            <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
            <?php }?>
        </span>

        <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create an options
            }

            foreach((array)$data['list_entries'] as $value => $value_data) {

                $field_id = $option_id.'-'.$value.'-field';?>

                <label for="<?php echo $field_id;?>">

                    <input type="radio" id="<?php echo $field_id;?>" name="<?php echo $option_id;?>" value="<?php echo $value;?>" <?php echo $data['value'] == $value ? 'checked' : '';?>>

                    <?php if(is_string($value_data)) {
                        echo esc_attr($value_data);
                    } else if(is_array($value_data) && array_key_exists('title', $value_data)) {

                        echo $value_data['title'];

                        if( !empty($value_data['description']) ) {?>
                        <span class="radio-entry-description"><?php echo $value_data['description']?></span>
                        <?php }

                        if( !empty($value_data['comment'])) {?>
                        <span class="field-q">
                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                            <span class="field-q-tooltip"><?php echo $value_data['comment']?></span>
                        </span>
                        <?php }

                    }?>

                </label>

            <?php }?>

        </span>

        <?php if( !empty($data['description']) ) {?>
        <div class="field-component help"><?php echo $data['description'];?></div>
        <?php }?>

    </div>

<?php }

// Select fields:
add_action('leyka_render_select', 'leyka_render_select_field', 10, 2);
function leyka_render_select_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                    <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">

                <?php if(is_string($data['list_entries'])) {
                    $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
                }?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>">
                    <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                        <option value="<?php echo $value;?>" <?php echo $value == $data['value'] ? 'selected' : '';?>><?php echo esc_attr($label);?></option>
                    <?php }?>
                </select>

            </span>

            <?php if( !empty($data['description']) ) {?>
            <div class="field-component help"><?php echo $data['description'];?></div>
            <?php }?>

        </label>

    </div>

<?php }

// Multi-select fields:
add_action('leyka_render_multi_select', 'leyka_render_multi_select_field', 10, 2);
function leyka_render_multi_select_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-multi-select-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">
            <?php if(is_string($data['list_entries'])) {
                $data['list_entries'] = $data['list_entries'](); // Call the callback to create select's options
            }?>

                <select id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" size="<?php echo $data['length'];?>">
                    <?php foreach((array)$data['list_entries'] as $value => $label) {?>
                        <option value="<?php echo $value;?>"><?php echo esc_attr($label);?></option>
                    <?php }?>
                </select>
            </span>

        </label>

    </div>

<?php }

// Textarea fields:
add_action('leyka_render_textarea', 'leyka_render_textarea_field', 10, 2);
function leyka_render_textarea_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;

    $data['value'] = isset($data['value']) ? $data['value'] : '';
    $data['is_code_editor'] = empty($data['is_code_editor']) || !in_array($data['is_code_editor'], array('css')) ?
        false : $data['is_code_editor'];?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-textarea-field-wrapper field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?> <?php echo $data['is_code_editor'] === 'css' ? 'css-editor' : '';?>">

        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <span class="field-component field">

                <textarea id="<?php echo $option_id.'-field';?>" name="<?php echo $option_id;?>" rows="" cols="" class="<?php echo $data['is_code_editor'] === 'css' ? 'css-editor-field' : '';?>"><?php echo esc_attr($data['value']);?></textarea>

            <?php if($data['is_code_editor'] === 'css') {?>

                <div class="css-editor-reset-value"><?php _e('Return original styles', 'leyka');?></div>
                <input type="hidden" class="css-editor-original-value" value="<?php echo $data['default'];?>">

            <?php }?>

            </span>

            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Simple HTML fields:
add_action('leyka_render_html', 'leyka_render_html_field', 10, 2);
function leyka_render_html_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-html-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id;?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <?php wp_editor(html_entity_decode(stripslashes($data['value'])), $option_id.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => false,
                'textarea_rows' => 3,
                'teeny' => true, // For little-functioned HTML editor
//                'dfw' => true,
            ));?>
            <?php if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

// Rich HTML fields:
add_action('leyka_render_rich_html', 'leyka_render_rich_html_field', 10, 2);
function leyka_render_rich_html_field($option_id, $data){

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $data['value'] = isset($data['value']) ? $data['value'] : '';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-rich-html-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">

        <label for="<?php echo $option_id;?>">

            <span class="field-component title">
                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required']) ? '' : '<span class="required">*</span>';?>
                <?php if( !empty($data['comment'])) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment']?></span>
                </span>
                <?php }?>
            </span>

            <?php wp_editor($data['value'], $option_id.'-field', array(
                'media_buttons' => false,
                'textarea_name' => $option_id,
                'tinymce' => true,
                'teeny' => true, // For rich HTML editor
//                'dfw' => true,
            ));

            if( !empty($data['description']) ) {?>
            <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

        </label>

    </div>

<?php }

add_action('leyka_render_colorpicker', 'leyka_render_colorpicker_field', 10, 2);
function leyka_render_colorpicker_field($option_id, $data) {

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-colorpicker-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) || !$data['field_classes'] ? '' : implode(' ', $data['field_classes']);?>">
        <label for="<?php echo $option_id.'-field';?>">

            <span class="field-component title">

                <span class="text"><?php echo $data['title'];?></span>
                <?php echo empty($data['required'] ? '' : '<span class="required">*</span>');

                if( !empty($data['comment']) ) {?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php echo $data['comment'];?></span>
                </span>
                <?php }?>

            </span>

            <?php if( !empty($data['description']) ) {?>
                <span class="field-component help"><?php echo $data['description'];?></span>
            <?php }?>

            <span class="field-component field">
                <input type="text" id="<?php echo $option_id.'-field';?>" class="leyka-setting-field colorpicker" value="<?php echo esc_attr($data['value']);?>" data-default-color="<?php echo empty($data['default']) ? '' : esc_attr($data['default']);?>">
                <input type="hidden" class="leyka-colorpicker-value" name="<?php echo $option_id;?>" value="<?php echo esc_attr($data['value']);?>">
            </span>

        </label>
    </div>

<?php }

function leyka_render_tabbed_section_options_area($section) {

    $default_active_tab_index = 0;
    
    if( !empty($section['tabs']) ) {?>

        <div class="section-tabs-wrapper">
        <div class="section-tab-nav">
            
        <?php
        $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {?>

            <a class="section-tab-nav-item <?php echo $counter === $default_active_tab_index ? 'active' : '';?>" href="#" data-target="<?php echo $tab_name;?>"><?php echo $tab['title'];?></a>

            <?php $counter += 1;

        }?>
        
        </div>

        <?php $counter = 0;
        foreach($section['tabs'] as $tab_name => $tab) {?>

            <div class="section-tab-content tab-<?php echo $tab_name;?> <?php echo $counter === $default_active_tab_index ? 'active' : '';?> <?php echo !empty($tab['screenshots']) ? 'with-sidebar' : '';?>">
                <div class="tab-content-options-wrapper">
                    <?php foreach($tab['sections'] as $tab_section) { ?>
                        <div class="tab-section-options">
                            
                            <?php if(!empty($tab_section['title'])) { ?>
                            <div class="field-component title tab-section-options-title">
                                <?php echo $tab_section['title'];?>
                            </div>
                            <?php } ?>
                            
                            <?php foreach($tab_section['options'] as $option) {
                                if(leyka_options()->is_template_option($option)) {
                                    $option = leyka_options()->get_tab_option_full_name($tab_name, $option);
                                }
                                
                                $option_info = leyka_options()->get_info_of($option);
                                do_action("leyka_render_{$option_info['type']}", $option, $option_info);
                            }?>
                        
                        </div>
                    <?php }?>
                </div>

                <?php if( !empty($tab['screenshots']) ) {?>
                <div class="tab-screenshots">
                    
                    <div class="tab-screenshot-nav left <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/icon-gallery-nav-arrow-left.svg';?>" alt="">
                    </div>

                    <?php $counter = 0;
                    foreach($tab['screenshots'] as $screenshot) {?>

                    <div class="tab-screenshot-item <?php echo !$counter ? 'active' : '';?>">
                        <div class="captioned-screen">

                            <div class="screen-wrapper">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/theme-screenshots/'.$screenshot;?>" class="leyka-instructions-screen" alt="">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/icon-zoom-screen.svg';?>" class="zoom-screen" alt="">
                            </div>

                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/theme-screenshots/'.$screenshot;?>" class="leyka-instructions-screen-full" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;" alt="">

                        </div>
                    </div>

                    <?php $counter += 1;

                    }?>

                    <div class="tab-screenshot-nav right <?php echo !empty($tab['screenshots']) && count($tab['screenshots']) > 1 ? 'active' : '';?>">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/icon-gallery-nav-arrow-right.svg';?>" alt="">
                    </div>

                </div>
                <?php }?>

            </div>

            <?php $counter += 1;

        }?>
        
        </div>
        <?php
    }
}

// [Special field] Support packages extension - packages list field:
add_action('leyka_render_custom_support_packages_settings', 'leyka_render_support_packages_settings', 10, 2);
function leyka_render_support_packages_settings($option_id, $data){ // support_packages_custom_packages_settings

    $option_id = stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
    $option_value = 'TODO';?>

    <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

        <div class="reward-box template"> <!-- style="display: none;" -->

            <h2 class="hndle ui-sortable-handle"><span><?php echo sprintf(__('Reward #%d', 'leyka'), 1);?></span></h2>

            <div class="box-content">

                <div class="option-block type-text">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('package_title', array(
                            'title' => __('Reward title', 'leyka'),
                            'placeholder' => __('E.g., "Golden support level"', 'leyka'),
                            'required' => true,
                        ));?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <div class="option-block type-text">
                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('package_slug', array(
                            'title' => __('Software title', 'leyka'),
                            'placeholder' => __('E.g., "golden"', 'leyka'),
                            'description' => __('Only latin & numeric characters allowed', 'leyka'),
                            'required' => true,
                        ));?>
                    </div>
                    <div class="field-errors"></div>
                </div>

                <div class="settings-block container-block">
                    <div class="container-entry" style="flex-basis: 47%;">
                        <div class="option-block type-number">
                            <div class="leyka-number-field-wrapper">
                                <?php leyka_render_number_field('package_min_amount', array(
                                    'title' => sprintf(__('Min donations amount, %s', 'leyka'), leyka_get_currency_label()),
                                    'placeholder' => '400',
                                    'required' => true,
                                ));?>
                            </div>
                            <div class="field-errors"></div>
                        </div>
                    </div>
                    <div class="container-entry" style="flex-basis: 47%;">
                        <div class="option-block type-number">
                            <div class="leyka-number-field-wrapper">
                                <?php leyka_render_number_field('package_max_amount', array(
                                    'title' => sprintf(__('Max donations amount, %s', 'leyka'), leyka_get_currency_label()),
                                    'placeholder' => '500',
                                    'required' => true,
                                ));?>
                            </div>
                            <div class="field-errors"></div>
                        </div>
                    </div>
                </div>

                <div class="settings-block option-block type-file">
                    <?php leyka_render_file_field('package_icon', array(
                        'upload_label' => __('Load icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
                        'required' => true,
                    ));?>
                    <div class="field-errors"></div>
                </div>

            </div>

            <div class="box-footer">
                <div class="delete-reward"><?php _e('Delete the reward', 'leyka');?></div>
            </div>

        </div>
        <div class="add-reward bottom"><?php _e('Add reward', 'leyka');?></div>

    </div>

<?php }
// [Special field] Support packages extension - packages list field - END
























