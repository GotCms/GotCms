<?php
namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor,
    Zend\Form\Element;

class PrevalueEditor extends AbstractPrevalueEditor
{

    public function save($request = null)
    {
        //Save prevalue in column Datatypes\prevalue_value
        $mime_list = $request->getParam('mime_list');
        $options_post = $request->getParam('options', array());
        $options = array();
        $options['multiple'] = in_array('multiple', $options_post) ? true : false;
        $options['title'] = in_array('title', $options_post)  ? true : false;
        $options['content'] = in_array('content', $options_post)  ? true : false;

        $this->setParameters(array('mime_list'=>$mime_list, 'options'=>$options));

        return $this->getParameters();
    }

    public function load()
    {
        $mime_list = new Element\MultiCheckbox('mime_list',array(
                'multiOptions' => array(
                'image/gif'=>'image/gif',
                'image/jpeg'=>'image/jpeg',
                'image/png'=>'image/png',
                'image/tiff'=>'image/tiff',
                'image/svg+xml'=>'image/svg+xml',
                'text/css'=>'text/css',
                'text/csv'=>'text/csv',
                'text/html'=>'text/html',
                'text/javascript'=>'text/javascript',
                'text/plain'=>'text/plain',
                'text/xml'=>'text/xml',
                'video/mpeg'=>'video/mpeg',
                'video/mp4'=>'video/mp4',
                'video/quicktime'=>'video/quicktime',
                'video/x-ms-wmv'=>'video/x-ms-wmv',
                'video/x-msvideo'=>'video/x-msvideo',
                'video/x-flv'=>'video/x-flv',
                'audio/mpeg'=>'audio/mpeg',
                'audio/x-ms-wma'=>'audio/x-ms-wma',
                'audio/vnd.rn-realaudio'=>'audio/vnd.rn-realaudio',
                'audio/x-wav'=>'audio/x-wav'
            )
        ));
        $parameters = $this->getParameters();
        $mime_list_values = isset($parameters['mime_list']) ? $parameters['mime_list'] : '';
        $mime_list->setValue($mime_list_values);
        $mime_list->setLabel('Type Mime accepted:');

        $options_values = isset($parameters['options']) ? $parameters['options'] : '';
        $upload_options = new Element\MultiCheckbox('options',array(
                'multiOptions' => array(
                'multiple'=>'multiple',
                'title'=>'has title',
                'content'=>'has content text'
            )
        ));
        $options = array();
        $options[] = isset($options_values['multiple']) && $options_values['multiple'] == true ? 'multiple' : '';
        $options[] = isset($options_values['title']) && $options_values['title'] == true ? 'title' : '';
        $options[] = isset($options_values['content']) && $options_values['content'] == true ? 'content' : '';
        $upload_options->setValue($options_values)->setLabel('Upload options:');
        return array($upload_options, $mime_list);
    }
}
