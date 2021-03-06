<?php

class actionAdminCtypesFieldsOptions extends cmsAction {

    public function run(){

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $ctype_name = $this->request->get('ctype_name', '');
        $field_id   = $this->request->get('field_id', 0);
        $field_type = $this->request->get('type', '');

        $field_class = 'field' . string_to_camel('_',  $field_type );

        $base_field = new $field_class(null, null);

        $options = $base_field->getOptions();
        $values  = false;

        if ($options && $ctype_name && $field_id) {

            $content_model = cmsCore::getModel('content');

            $field = $content_model->getContentField($ctype_name, $field_id);

            $values = $field['options'];

        }

        $this->cms_template->render('ctypes_field_options', array(
            'is_can_in_filter' => ($base_field->filter_type !== false),
            'options'          => $options,
            'values'           => $values
        ));

    }

}