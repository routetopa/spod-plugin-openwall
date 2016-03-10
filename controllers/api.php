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
        //$lintHost = parse_url($p, PHP_URL_HOST);
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
                    //'provider_name' => parse_url($p, PHP_URL_HOST),
                    'provider_name' => 'p:' . $p->id,
                    'resource_name' => array_key_exists('name', $r) ? $r['name'] : $r['description'],
                    'package_name' => $ds['title'],
                    'organization_name' => array_key_exists('organization', $ds) ? $ds['organization']['title'] : '',
                    'url' => $r['url'],
                    //'url' => str_replace(parse_url($r['url'], PHP_URL_HOST), $lintHost, $r['url']),
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

            @$treemapdata[] = array(
                'provider_name' => 'p:' . $p->id,
                'organization_name' => $ds['metas']['publisher'],
                'package_name' => $ds['metas']['title'],
                'resource_name' => 'Click to open', //array_key_exists('name', $r) ? $r['name'] : $r['description'],
                'url' => $p->api_url . '/explore/dataset/' . $ds['datasetid'],
                'w' => 1
            );
        }
        return $treemapdata;
    }

    public function datasetTreeBuilder()
    {
        $providers = OPENWALL_BOL_Service::getInstance()->getProviderList();

        /*
        $providers = null;
        $hostname = gethostname();
        switch ($hostname) {
            case 'prato.routetopa.eu';
                $providers = [
                    'http://dati.lazio.it/catalog' ];
                break;
            case 'issy.routetopa.eu';
                $providers = [
                    'https://data.issy.com',
                    'http://data.iledefrance.fr' ];
                break;
            case 'dublin.routetopa.eu';
                $providers = [
                    'https://data.gov.ie' ];
                break;
            case 'denhaag.routetopa.eu';
            case 'groningen.routetopa.eu';
                $providers = [
                    'https://data.overheid.nl/data' ];
                break;
            default:
                $providers = [
                    'https://data.issy.com',
                    'http://dati.lazio.it/catalog',
                    'https://data.gov.uk',
                    'https://data.overheid.nl/data',
                    'http://data.iledefrance.fr',
                    'https://data.gov.ie' ];
                break;
        }
        $providers[] = 'http://ckan.routetopa.eu';
        $providers[] = 'http://vmdatagov01.deri.ie:8080';
        */

        $providersdata = [];
        $treemapdata = [];
        foreach ($providers as $p) {
            // Build providers info
            $providersdata[$p->id] = $p;

            // Try CKAN
            $ch = curl_init($p->api_url . "/api/3/action/package_search?rows=1000");//1000 limit!
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
                $treemapdata = array_merge($treemapdata,  $this->buildCkanTree($p, $data));
                continue;
            }

            // Try ODS
            $ch = curl_init($p->api_url . "/api/datasets/1.0/search/?rows=-1");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
                $treemapdata = array_merge($treemapdata,  $this->buildOpenDataSoftTree($p, $data));
                continue;
            }
        }

        return json_encode( array( 'result' => array( 'providers' => $providersdata, 'datasets' => $treemapdata )));
    }

    public function datasetTree()
    {
        header('content-type: application/json');
        header("Access-Control-Allow-Origin: *");
        echo $this->datasetTreeBuilder();
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