<?php

class fieldCity extends cmsFormField {

    public $title   = LANG_PARSER_CITY;
    public $sql     = 'int(11) NULL DEFAULT NULL';
    public $filter_type = 'city';
    public $filter_hint = LANG_PARSER_CITY_FILTER_HINT;

	public $add_fields = array(
		'region' => array(
			'sql' => 'int(11) NULL DEFAULT NULL',
			'allow_index' => true
		),
		'country' => array(
			'sql' => 'int(11) NULL DEFAULT NULL',
			'allow_index' => true
		)
	);

    public function getOptions(){
        return array(

            new fieldCheckbox('city_name', array(
                'title' => LANG_PARSER_CITY_CITY_NAME,
				'default' => 1
            )),

            new fieldCheckbox('region_name', array(
                'title' => LANG_PARSER_CITY_REGION_NAME,
				'default' => 0
            )),

            new fieldCheckbox('country_name', array(
                'title' => LANG_PARSER_CITY_COUNTRY_NAME,
				'default' => 0
            )),
        );
    }

    public function getInput($value) {

        if (is_numeric($value)){
            $value = $this->getCity($value);
        }

        return parent::getInput($value);

    }

    public function getFilterInput($value) {

		$request = cmsCore::getInstance()->request;
		$user = cmsUser::getInstance();
		$geo_model = cmsCore::getModel('geo');

        $countries = $geo_model->getCountries();
        $countries = array('0'=>LANG_PARSER_CITY_SELECT_COUNTRY) + $countries;

		$city_id = $request->get($this->name);
        $region_id = $request->get($this->name.'_region');
        $country_id = $request->get($this->name.'_country');;

        $regions = array();
        $cities = array();

//        if ($user->is_logged && !$city_id && $user->city['id'] && !$region_id && !$country_id){
//            $city_id = $user->city['id'];
//        }
//
//        if ($city_id && !$region_id && !$country_id){
//
//            $city_parents = $geo_model->getCityParents($city_id);
//
//            $region_id = $city_parents['region_id'];
//            $country_id = $city_parents['country_id'];
//
//        }

		if ($country_id){
            $regions = $geo_model->getRegions($country_id);
            $regions = array('0'=>LANG_PARSER_CITY_SELECT_REGION) + $regions;
		}

		if ($region_id){
            $cities = $geo_model->getCities($region_id);
            $cities = array('0'=>LANG_PARSER_CITY_SELECT_CITY) + $cities;
		}

		cmsTemplate::getInstance()->addJS('templates/default/js/geo.js');
		$html = '';

		$html .= '<div id="filter_geo_window">';
		$html .= '<div class="list">'.html_select($this->name.'_country', $countries, $country_id, array('onchange'=>'icms.geo.filterChangeParent(this, \''.$this->name.'_region\')', 'rel'=>'countries')).'</div>';
		$html .= '<div class="list"' . (!$regions ? ' style="display:none"' : '') . '>'.html_select($this->name.'_region', $regions, $region_id, array('onchange'=>'icms.geo.filterChangeParent(this, \''.$this->name.'\')', 'rel'=>'regions')).'</div>';
		$html .= '<div class="list"' . (!$cities ? ' style="display:none"' : '') . '>'.html_select($this->name, $cities, $city_id, array('rel'=>'cities')).'</div>';
		$html .= '</div>';
        return $html;

    }

    private function getCity($id){

		$geo_model = cmsCore::getModel('geo');
        $city = $geo_model->getCity($id);
        $city_parents = $geo_model->getCityParents($id);

        $value = $city ? array(
            'id' => $city['id'],
            'name' => $city['name'],
            'region_id' => $city_parents['region_id'],
            'country_id' => $city_parents['country_id']
        ) : false;

        return $value;

    }

    public function parse($value){

		$result = array();

		$geo_model = cmsCore::getModel('geo');

		$city = $geo_model->getCity($value);

		if ($this->getOption('city_name')){
            $result[] = $city['name'];
        }

		if ($this->getOption('region_name')){
            $regions = $geo_model->getRegions($city['country_id']);
            $result[] = $regions[$city['region_id']];
        }

		if ($this->getOption('country_name')){
            $countries = $geo_model->getCountries();
            $result[] = $countries[$city['country_id']];
        }

		if (!$this->getOption('city_name') && !$this->getOption('region_name') && !$this->getOption('country_name')){
            $result[] = $city['name'];
        }

        return htmlspecialchars(implode(', ', $result));
    }

    public function applyFilter($model, $value) {

		$request = cmsCore::getInstance()->request;
		if($request->has($this->name)){
			$value = (int)$request->get($this->name);
			if ($value > 0){ $model->filterEqual($this->name, "{$value}"); return $model;}
		}
		if($request->has($this->name . '_region')){

			$value = (int)$request->get($this->name . '_region');
			if ($value > 0){$model->filterEqual($this->name . '_region', "{$value}"); return $model;}
		}
		if($request->has($this->name . '_country')){
			$value = (int)$request->get($this->name . '_country');
			if ($value > 0){$model->filterEqual($this->name . '_country', "{$value}"); return $model;}
		}

        return $model;
    }

}
