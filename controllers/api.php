<?php

class OPENWALL_CTRL_Api extends OW_ActionController
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));
        $this->setDocumentKey('openwall_index_page');
    }

    private function buildCkanTree($p, $data) {
        $treemapdata = array();
        $datasets = $data['result']['results'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];
            $resources = $ds['resources'];
            $resourcesCnt = count( $resources );
            for ($j = 0; $j < $resourcesCnt; $j++) {
                $r = $resources[$j];
                $treemapdata[] = array(
                    'provider_name' => parse_url($p, PHP_URL_HOST),
                    'resource_name' => array_key_exists('name', $r) ? $r['name'] : $r['description'],
                    'package_name' => $ds['title'],
                    'organization_name' => array_key_exists('organization', $ds) ? $ds['organization']['title'] : '',
                    'url' => $r['url'],
                    'w' => 1
                );
            }
        }
        return $treemapdata;
    }

    private function buildOpenDataSoftTree($p, $data) {
        $treemapdata = array();
        $datasets = $data['datasets'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];

            $treemapdata[] = array(
                'provider_name' => parse_url($p, PHP_URL_HOST),
                'organization_name' => $ds['metas']['publisher'],
                'package_name' => $ds['metas']['title'],
                'resource_name' => 'Click to open', //array_key_exists('name', $r) ? $r['name'] : $r['description'],
                'url' => $p . '/explore/dataset/' . $ds['datasetid'],
                'w' => 1
            );
        }
        return $treemapdata;
    }

    public function datasetTree()
    {
        $preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');
        $odProvider = empty($preference) ? "http://ckan.routetopa.eu" : $preference->defaultValue;

        /*
        $odProvider = 'https://data.issy.com';
        $odProvider .= ',http://ckan.routetopa.eu';
        $odProvider .= ',http://dati.lazio.it/catalog';
        $odProvider .= ',https://data.gov.uk';
        $odProvider .= ',http://vmdatagov01.deri.ie:8080';
        $odProvider .= ',https://data.overheid.nl/data';
        $odProvider .= ',https://data.gov.ie';
        */

        $providers = explode(',', $odProvider);
        $treemapdata = [];
        foreach ($providers as $p) {
            // Try CKAN
            $ch = curl_init("$p/api/3/action/package_search");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
                $treemapdata = array_merge($treemapdata,  $this->buildCkanTree($p, $data));
                continue;
            }

            // Try ODS
            $ch = curl_init("$p/api/datasets/1.0/search/");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
                $treemapdata = array_merge($treemapdata,  $this->buildOpenDataSoftTree($p, $data));
                continue;
            }
        }

        header('content-type: application/json');
        echo json_encode( array( 'result' => $treemapdata ));
        die();
    }

    // http://localhost/openwall/api/t
    public function t() {
        $preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');
        $odProvider = empty($preference) ? "http://ckan.routetopa.eu" : $preference->defaultValue;


        echo $odProvider;
        die();
    }

}