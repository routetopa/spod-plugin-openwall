<?php

class OPENWALL_CTRL_Api extends OW_ActionController
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('openwall', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('openwall', 'index_page_heading'));
        $this->setDocumentKey('openwall_index_page');
    }

    protected function sanitizeInput($str)
    {
        return str_replace("'", "&#39;", !empty($str) ? $str : "");
    }

    private function buildCkanTree($p, $data) {
        $filter = array("id", "creator_user_id", "license_id", "owner_org", "revision_id");

        $treemapdata = array();
        $datasets = $data['result']['results'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];
            $resources = $ds['resources'];
            $resourcesCnt = count( $resources );
            for ($j = 0; $j < $resourcesCnt; $j++) {
                $r = $resources[$j];

                $metas = [
                    "name" => $r['name'],
                    "description" => $r['description'],
                    "format" => $r['format'],
                    "created" => $r['created']
                ];

                if($r['last_modified'] != null)
                    $metas['last_modified'] = $r['last_modified'];

                $metas['organization'] = array_key_exists('organization', $ds) ? $ds['organization']['title'] : '';


                foreach ($ds as $key => $value) {
                    if(!in_array($key, $filter) and gettype($value) == "string" and $value != null and $value != "")
                        if(!$metas[$key])
                            $metas[$key] = $value;
                }

                $treemapdata[] = array(
                    'w' => 1,
                    'provider_name' => $this->sanitizeInput('p:' . $p->id),
                    'organization_name' => $this->sanitizeInput(array_key_exists('organization', $ds) ? $ds['organization']['title'] : ''),
                    'package_name' => $this->sanitizeInput($ds['title']),
                    'resource_name' => $this->sanitizeInput(array_key_exists('name', $r) ? $r['name'] : $r['description']),
                    'url' => $r['url'],
//                    'metas' => json_encode(["organization" => $this->sanitizeInput(array_key_exists('organization', $ds) ? $ds['organization']['title'] : ''),
//                        "name" => $this->sanitizeInput($r->name),
//                        "description" => $this->sanitizeInput($r->description),
//                        "format" => $this->sanitizeInput($r->format),
//                        "created" => $this->sanitizeInput($r->created),
//                        "last_modified" => $this->sanitizeInput($r->last_modified)
//                    ])
                    'metas' => $this->sanitizeInput(json_encode($metas))
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
                'w' => 1,
                'provider_name' => $this->sanitizeInput('p:' . $p->id),
                'organization_name' => $this->sanitizeInput($ds['metas']['publisher']),
                'package_name' => $this->sanitizeInput($ds['metas']['title']),
                'resource_name' => $this->sanitizeInput($ds['metas']['title']),
                'url' => $p->api_url . '/explore/dataset/' . $ds['datasetid'],
                'metas' => $this->sanitizeInput(json_encode($ds['metas']))
            );
        }
        return $treemapdata;
    }

    public function datasetTreeBuilder()
    {
        $providersdata = [];
        $treemapdata = [];
        $step = 1;
        $maxDatasetPerProvider = isset($_REQUEST['maxDataset']) ? $_REQUEST['maxDataset'] : 1; //500

        $providers = OPENWALL_BOL_Service::getInstance()->getProviderList();

        foreach ($providers as $p) {

            // Build providers info
            $providerDatasetCounter = 0;
            $start = 0;
            $providersdata[$p->id] = $p;

            // Try CKAN
            while($providerDatasetCounter < $maxDatasetPerProvider) {
                $ch = curl_init($p->api_url . "/api/3/action/package_search?start=" . $start . "&rows=" . $step);//1000 limit!
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $res = curl_exec($ch);
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (200 == $retcode)
                {
                    $data = json_decode($res, true);

                    if(count($data["result"]["results"]))
                    {
                        $treemapdata = array_merge($treemapdata, $this->buildCkanTree($p, $data));
                        $start += $step;
                        $providerDatasetCounter += count($data["result"]["results"]);
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    break;
                }
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

