<?php
/**
 * Created by PhpStorm.
 * User: Arnouxor
 * Date: 14/07/15
 * Time: 00:28
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Le but de cette librarie est d'unifier le traitement des appels ajax pour qu'il ait toujours la même gueule
 * Class Ajax_lib
 */
class Ajaxosor
{

    /* CI instance */
    private $CI;

    /* will contain the results */
    private $results;

    /* data sent in ajax call */
    private $data;

    public function __construct()
    {
        $this->CI = &get_instance();

        $this->CI->load->library("secure");

    }

    /**
     * Traitement général de la requête ajax
     * @param $config , la config pour valider les champs avec le form_validation
     * @param $type , le type de fichier contenant le code de traitement, un model ou une libraire
     * @param $file , la lib ou le model contenant le code de traitement
     * @param $method , la méthode contenant le code de traitement
     * @param string , $msg le message ajax de retour
     * @param null , $form_id l'id du formulaire (pour traitemant automatique des erreurs en retour)
     */
    public function process($config, $type, $file, $method, $msg = "", $form_id = null)
    {
        /* a TRUE si il y a une erreur */
        $b_error = FALSE;

        $this->results = array(
            "status" => FALSE,
            "value" => "",
            "error" => array(),
            "form_id" => $form_id,
            "msg" => $msg
        );

        // vérifie si c'est bien un appel ajax
        if (!$this->CI->input->is_ajax_request()) {
            $this->results["error"]["request"] = 'No direct script access allowed';
            $b_error = TRUE;
        }

        if ($type != "library" && $type != "model") {
            $this->results["error"]["type"] = 'Le type ne peut prendre comme valeur que "libraries" ou "models" ';
            $b_error = TRUE;
        } else {
            switch ($type) {
                default:
                case "library" :
                    $typepath = "libraries";
                    break;
                case "model" :
                    $typepath = "models";
                    break;
            }

            #TODO vérifier si le fichier existe (marche pas jsais pas pourquoi)
            if (!file_exists(FCPATH . APPPATH . $typepath . "/" . $file) && FALSE) {
                $this->results["error"]["file"] = "Le fichier de traitement spécifié n'existe pas !";
                $b_error = TRUE;
            }

            #TODO vérifier si la méthode existe
            if (!method_exists($file, $method) && FALSE) {
                $this->results["error"]["method"] = "La méthode spécifiée n'existe pas !";
                $b_error = TRUE;
            }
        }


        // Cette librairie rend obligatoire la vérification des données entrantes
        if (empty($config)) {
            $this->results["error"]["config"] = "Vous devez absoluement vérifier les données entrante !";
            $b_error = TRUE;
        }

        // on enlève la dimension alias au tableau config
        foreach ($config as $key => $rule) {
            $form_config[] = array("field" => $rule["field"], "label" => $rule["label"], "rules" => $rule["rules"]);


        }

        $this->CI->form_validation->set_rules($form_config);

        if ($this->CI->form_validation->run() === FALSE) {

            $this->results["error"] = json_encode($this->CI->form_validation->get_errors());
            $b_error = TRUE;
        } else {
            // si vérification ok on stock les variables envoyées
            $this->data = $this->CI->input->post();

            // on fait les post traitement (transformation en objet...)

            foreach ($config as $key => $rule) {
                // on en profite pour mettre les alias dans un tableau
                if (!empty($rule["transformation"])) {
                    $transfo_rules = explode("|", $rule["transformation"]);

                    $var = $this->CI->input->post($rule["field"]);

                    // traitement des règles de transformation
                    foreach ($transfo_rules as $tranform_method) {
                        $particules = explode("=", $tranform_method);

                        // decryptage de variable
                        if (strpos($tranform_method, "decrypt") !== FALSE) {
                            $result = $this->CI->secure->decrypt($var, $particules[1], FALSE); // on décrypt l'id user
                        }

                        // transformation de variable en objet
                        if (strpos($tranform_method, "object") !== FALSE) {
                            $object_name = $particules[1];
                            $object = new $object_name($var);
                            $result = $object;
                        }

                        // garde en mémoire le changement précédent pour la réutiliser par la suite
                        $var = $result;

                        // change la variable après transformation
                        $this->data[$rule["field"]] = $result;

                    }
                }

            }
        }

        // s'il n'y a pas d'erreur
        if ($b_error === FALSE) {
            $this->traitement($type, $file, $method);
        }

        $this->CI->output->set_output(json_encode($this->results));
    }

    /**
     * Fonction appelée pour traitement (Forcer le développeur à mettre son code dans une lib ou un model)
     * @param $type
     * @param $file
     * @param $method
     */
    private function traitement($type, $file, $method)
    {
        $b_success = FALSE;

        $this->CI->load->{$type}($file);

        foreach($this->data as $key => $value) {
            $params[] = $value;
        }
        $nb_params = count($this->data);

        if ($nb_params == 1 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0])))
            $b_success = TRUE;

        if ($nb_params == 2 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1])))
            $b_success = TRUE;

        if ($nb_params == 3 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2])))
            $b_success = TRUE;

        if ($nb_params == 4 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3])))
            $b_success = TRUE;

        if ($nb_params == 5 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4])))
            $b_success = TRUE;

        if ($nb_params == 6 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4], $params[5])))
            $b_success = TRUE;

        if ($nb_params == 7 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6])))
            $b_success = TRUE;

        if ($nb_params == 8 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7])))
            $b_success = TRUE;

        if ($nb_params == 9 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8])))
            $b_success = TRUE;

        if ($nb_params == 10 && ($this->results["value"] = $this->CI->{strtolower($file)}->{$method}($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9])))
            $b_success = TRUE;

        if($b_success === TRUE) {
            $this->results["status"] = TRUE;
        }

    }


    /**
     * Retour de l'appel AJAX
     */
    public function ajaxOutput()
    {

        if (is_object($this->results["value"]) || is_array($this->results["value"])) {
            $this->results["value"] = json_encode($this->results["value"]);
        }
        $this->CI->output->set_output(json_encode($this->results));
    }

}

