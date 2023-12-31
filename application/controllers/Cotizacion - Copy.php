<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

require APPPATH . 'third_party/tcpdf/tcpdf.php';
//require APPPATH . 'third_party/PHPMailer3/src/PHPMailer.php';
//require APPPATH . 'third_party/PHPMailer3/src/Exception.php';
//require APPPATH . 'third_party/PHPMailer3/src/SMTP.php';
class MYPDF extends TCPDF {
    //Page header
    public function Header() {
		$img = base_url('styles/bg/background3.jpg');
        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->AutoPageBreak;
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set bacground image
        $this->Image($img, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();
    }
}

class Cotizacion extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->helper('url');

		$this->load->model("user_model");

		if (!$this->user->loggedin) {
			redirect(base_url());
		}

		$this->load->library('grocery_CRUD');

	}

	public function _example_output($output = null) {
		//$this->load->view('example.php', (array) $output);
		//$this->template->loadData("activeLink",
		//	array("sedes" => array("index" => 1)));
		$this->template->loadContent("crud_simple.php", (array) $output);

	}
	function index() {
        if (!$this->common->has_permissions(array("quotation_index"), $this->user)) {
            if (!$this->common->has_permissions(array("quotation_operation"), $this->user)) {
                $this->template->error(lang("error_2"));
            }
//            $this->template->error(lang("error_2"));
            redirect(site_url('cotizacion/operacion'));
        }
		$this->template->loadData("activeLink",
			array("cotizacion" => array("index" => 1)));
		$this->template->loadContent("cotizacion/index_cotizacion", array(
			"new_members" => 0,
			"stats" => 0,
			"online_count" => 0,
		)
		);

	}

	function _select($array = array()) {
		$r = array();

		foreach ($array as $k => $v) {
			$r[$v['id']] = $v;
		}

		return $r;
	}
	function crear() {
		redirect(site_url('cotizacion/operacion'));
	}
	function operacion($_param_cotizacion_id = 0) {
		/* formulario de creacion de cotizacion */
        if (!$this->common->has_permissions(array("quotation_operation"), $this->user)) {
            $this->template->error(lang("error_2"));
        }

		$sedes_id = _helper_sedes_id();

		if ($sedes_id === false) {
			// en caso de que no ha sido seteado
			//_helper_set_sede();

			_helper_my_sedes();
			redirect("", 'refresh');
		}

		$this->template->loadData("activeLink",
			array("cotizacion" => array("crear" => 1)));

		$monedas = $this->db->select('  CountryCode as id, concat(CountryCode,"-",CountryName) as value')->get('countrycode')->result_array();
		$monedas = $this->_select($monedas);

		$terminospago = $this->db->select('  value as id, description as value  ')->get('ce_config_terminospago')->result_array();
		$terminospago = $this->_select($terminospago);
		//---------------------------------------------
		//ce_config_incoterms

		$ce_config_incoterms = $this->db->select('  value as id, description as value  ')->get('ce_config_incoterms')->result_array();
		$ce_config_incoterms = $this->_select($ce_config_incoterms);
		//---------------------------------------------
		//ce_config_incoterms

		$ce_config_plazo = $this->db->select('  value as id, description  as value ')->get('ce_config_plazo')->result_array();
		$ce_config_plazo = $this->_select($ce_config_plazo);
		//---------------------------------------------
		//ce_config_incoterms

		$ce_config_tiemposentrega = $this->db->select('  value as id, description  as value ')->get('ce_config_tiemposentrega')->result_array();
		$ce_config_tiemposentrega = $this->_select($ce_config_tiemposentrega);
		//---------------------------------------------
		//ce_config_incoterms

		$ce_config_validezpropuesta = $this->db->select('  value as id, description  as value ')->get('ce_config_validezpropuesta')->result_array();
		$ce_config_validezpropuesta = $this->_select($ce_config_validezpropuesta);
		//---------------------------------------------
		$ce_empresas = $this->db->select('  empresas_id as id, nombre  as value ')->get('ce_empresas')->result_array();
		$ce_empresas = $this->_select($ce_empresas);
		//---------------------------------------------

		//---------------------------------------------
		$ce_config_marcado = $this->db->select('  value as id, description  as value ')->get('ce_config_marcado')->result_array();
		$ce_config_marcado = $this->_select($ce_config_marcado);
		//---------------------------------------------

		//---------------------------------------------
		$ce_config_tiemposentrega = $this->db->select('  value as id, description  as value ')->get('ce_config_tiemposentrega')->result_array();
		$ce_config_tiemposentrega = $this->_select($ce_config_tiemposentrega);
		//---------------------------------------------
		$ce_config_impuesto = $this->db->select('  value as id, description  as value ')->get('ce_config_impuesto')->result_array();
		$ce_config_impuesto = $this->_select($ce_config_impuesto);
		//---------------------------------------------

		//ce_config_incoterms

		//ce_config_incoterms
		//ce_config_terminospago

		/* verificamos si es una CREACION o un UPDATE */
		//	$_post_cotizacion_id = $this->input->post('cotizacion_id');

		//	if (isset($_post_cotizacion_id) && !empty($_post_cotizacion_id)) {
		//		$cotizacion_id = (int) $_post_cotizacion_id;
		//	} else {
		$cotizacion_id = $_param_cotizacion_id;
		//	}
		$cotizacion = array();
		$cotizacion[0] = array();
		$cotizacion[1] = array();
		$cotizacion[2] = array();
		if ($cotizacion_id > 0) {
			$cotizacion = $this->cotizacion_model->get_by_id($cotizacion_id);

		} else {
			/*creando*/
		}

		/*OBTENEMOS VARIABLES*/

		/*obteniendo  porcentaje de  comision */

		/***************************************/

		$data['terminospago'] = $terminospago;
		$data['incoterms'] = $ce_config_incoterms;
		$data['plazo'] = $ce_config_plazo;
		$data['tiemposentrega'] = $ce_config_tiemposentrega;
		//print_r($ce_config_validezpropuesta);
		$data['validezpropuesta'] = $ce_config_validezpropuesta;
		$data['empresas'] = $ce_empresas;
		$data['marcado'] = $ce_config_marcado;
		$data['tiemposentrega'] = $ce_config_tiemposentrega;
		$data['impuesto'] = $ce_config_impuesto;
		$data['cotizacion_id'] = $cotizacion_id;
		$data['cotizacion_info'] = $cotizacion[0];

		$this->template->loadContent("cotizacion/operacion", $data
		);
	}
	function send_email() {
		$lista_emails = $this->input->post('lista-emails');
		$mensaje = $this->input->post('mensaje');
		$cotizacion_id = $this->input->post('cotizacion_id');
		if (empty($lista_emails)) {
			_json_error("Ingrese un email");
		}
		$lista_emails_array = explode(',', $lista_emails);

		/* crear pdf */
		$this->pdf($cotizacion_id, true);
		/* fin crear PDF */
		$_emails_validos = array();
		foreach ($lista_emails_array as $k => $v) {
			if (!empty($v)) {
				$_emails_validos[] = $v;
			}

		}
		$this->load->library('email');
		//$CI->email->initialize($config);
		$this->email->from($this->settings->info->site_email, $this->settings->info->site_name);
		$this->email->to($_emails_validos);
		$nombre = _helper_cotizacion_name($cotizacion_id);
		//$path = 'G:\wamp20162\www\freelancer\CAPSA/cotizaciones/';

		$path = $this->config->item('cotizaciones_path');

		$atch = $path . $nombre;
		$this->email->subject("Envio de cotización");
		$this->email->message($mensaje);
		$this->email->attach($atch);
		$temp = $this->email->send();

		if ($temp == true) {
			_json_ok("El archivo fue enviado.");
		} else {
			_json_error("Ocurrio un error al enviar el archivo.");
		}
	}
	function test_mail2() {
		$subject = 'SUBJET';
		$body = "body";
		$emailt = 'ing.renee.sis@gmail.com';
		$temp = $this->common->send_email($subject, $body, $emailt);

		echo var_dump($temp);
	}
	public function test_mail() {

		$this->load->library("phpmailer_library");
		$mail = $this->phpmailer_library->load();

//Enable SMTP debugging.
		$mail->SMTPDebug = 1;
//Set PHPMailer to use SMTP.
		$mail->isSMTP();
//Set SMTP host name
		$mail->Host = "mail.reneemorales.com";
//Set this to true if SMTP host requires authentication to send email
		$mail->SMTPAuth = true;
//Provide username and password
		$mail->Username = "mail@reneemorales.com";
		$mail->Password = "97856832";
//If SMTP requires TLS encryption then set it
		//$mail->SMTPSecure = "tls";
		//Set TCP port to connect to
		$mail->Port = 25;

		/*	$log = $this->common->send_email("titulo",
			"Mensaje", "ing.renee.sis@gmail.com");
*/
		$mail->From = "ing.renee.sis@gmail.com";
		$mail->FromName = "Full Name";

//To address and name
		$mail->addAddress("ing.renee.sis+tutores@gmail.com", "Recepient Name");
		//$mail->addAddress("recepient1@example.com"); //Recipient name is optional

//Address to which recipient will reply
		//$mail->addReplyTo("reply@yourdomain.com", "Reply");

//CC and BCC
		//	$mail->addCC("cc@example.com");
		//	$mail->addBCC("bcc@example.com");

//Send HTML or Plain Text email
		$mail->isHTML(true);

		$mail->Subject = "Subject Text";
		$mail->Body = "<i>Mail body in HTML</i>";
		$mail->AltBody = "This is the plain text version of the email content";

		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			echo "Message has been sent successfully";
		}
		echo "<pre>";
		echo var_dump($mail);
		echo "</pre>";
	}
	function pdf($cotizacion_id, $save = false) {
		$html = 'La cotizacion no existe';

        if (empty($cotizacion_id)) {
            $cotizacion_id = $this->session->userdata('cotizacion_id');
            // $this->session->unset_userdata('cotizacion_id');
        } else {
            $this->session->set_userdata('cotizacion_id', $cotizacion_id);
        }

		if ($cotizacion_id > 0) {
			$cotizacion = $this->cotizacion_model->get_by_id($cotizacion_id);
			$data['cotizacion_id'] = $cotizacion_id;
			$data['cotizacion_info'] = $cotizacion[0];

			$vehiculos_id = $cotizacion[0]['vehiculos_id'];

			$vehiculo = $this->db->where('vehiculos_id', $vehiculos_id)->get('ce_vehiculos')->row_array();
			$data['cotizacion_info']['vehiculo_nombre'] = $vehiculo['name'];
			$data['cotizacion_info']['vehiculo_descripcion'] = $vehiculo['description'];
			$data['cotizacion_info']['vehiculo_logo'] = $vehiculo['logo'];
			$data['cotizacion_info']['vehiculo_tipo_unidad'] = $vehiculo['tipo_unidad'];
            $data['cotizacion_info']['vehiculo_price'] = $vehiculo['price'];
			$data['save'] = $save;
//            echo var_dump($data);

			$html = $this->load->view('cotizacion/cotizacion_pdf_2', $data, true);
		}

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);
		$pdf->setPrintFooter(false);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->AddPage();
		$pdf->writeHTML($html);

//        echo "start";
        $file = $cotizacion_id.".pdf";
//        echo $_SERVER;
//        return;
        $pdf->Output($_SERVER['DOCUMENT_ROOT']."/uploads/pdf/" . $file, 'FI');


        $mail = new PHPMailer;
        $mail->isSMTP();                                // Set mailer to use SMTP
        $mail->Host = SmtpServer;                       // SMTP server
        $mail->SMTPAuth = true;                         // Enable SMTP authentication
        $mail->Username = SmtpUsername;                 // SMTP username
        $mail->Password = SmtpPassword;                 // SMTP password
        $mail->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
        $mail->From = FromEmail;
        $mail->Port = 587;                              // SMTP Port
        $mail->FromName  = 'testing';

        $mail->Subject   = 'Hello';
        $mail->Body      = 'Nice to meet you';
        $mail->AddAddress('ding.w98106@gmail.com');
        $mail->AddAttachment("./uploads/OrderDetails.pdf", '', $encoding = 'base64', $type = 'application/pdf');
        return $mail->Send();
//        echo "hello";

		//$file = $cotizacion_id.".pdf";
//	  	$filelocation = "uplaod/pdf";
//        $fileNLfileNL = $filelocation."/".$file;
		//$pdf->Output($file, 'I');
//		$pdf->Output($fileNLfileNL, 'F');
        // $pdf->Output(dirname(__DIR__).'/uploads/pdf/'.$cotizacion_id.'.pdf', 'F');
//        printf(dirname(__DIR__));
//        return;
//        $email = _info('email', $cotizacion_info, true);
//        $mail = new PHPMailer;
//        $mail->isSMTP();                                // Set mailer to use SMTP
//        $mail->Host = SmtpServer;                       // SMTP server
//        $mail->SMTPAuth = true;                         // Enable SMTP authentication
//        $mail->Username = SmtpUsername;                 // SMTP username
//        $mail->Password = SmtpPassword;                 // SMTP password
//        $mail->SMTPSecure = 'tls';                      // Enable TLS encryption, `ssl` also accepted
//        $mail->From = 'ventas@transportesalfra.com.mx';
//        $mail->Port = 587;                              // SMTP Port
//        $mail->FromName  = 'testing';
//
//        $mail->Subject   = 'TRANSPORTES DEDICADOS';
//        $mail->Body      = $html;
//        $mail->AddAddress($email);
//        $mail->addStringAttachment($pdf->Output("S",'OrderDetails.pdf'), 'OrderDetails.pdf', $encoding = 'base64', $type = 'application/pdf');
//        return $mail->Send();


		// if ($save === false) {
		// 	$pdf->Output($pdfFilePath, 'I');
		// } else {
		// 	$nombre = _helper_cotizacion_name($cotizacion_id);
		// 	//$path = 'G:\wamp20162\www\freelancer\CAPSA/cotizaciones/';
		// 	$path = $this->config->item('cotizaciones_path');
		// 	$file = $path . $nombre;
		// 	$this->m_pdf->pdf->Output($file, 'F');
		// }

		/*$this->load->library('m_pdf');
		$this->m_pdf->pdf->WriteHTML($html);
		$this->m_pdf->pdf->useSubstitutions=false;
		$this->m_pdf->pdf->setAutoTopMargin = 'stretch';
		$this->m_pdf->pdf->SetDisplayMode('fullpage');
		if ($save === false) {
			$this->m_pdf->pdf->Output($pdfFilePath, "I");
		} else {
			$nombre = _helper_cotizacion_name($cotizacion_id);
			//$path = 'G:\wamp20162\www\freelancer\CAPSA/cotizaciones/';
			$path = $this->config->item('cotizaciones_path');
			$file = $path . $nombre;
			$this->m_pdf->pdf->Output($file, 'F');
		}*/
	}
	function pdf2($cotizacion_id, $save = false) {
		//$this->load->library('pdf');

		//$this->load->view('welcome_message');

		// Get output html
		//$html = $this->output->get_output();
		$html = 'La cotizacion no existe';
		if ($cotizacion_id > 0) {
			$cotizacion = $this->cotizacion_model->get_by_id($cotizacion_id);
			$data['cotizacion_id'] = $cotizacion_id;
			$data['cotizacion_info'] = $cotizacion[0];

			$data['save'] = $save;
			$this->load->view('cotizacion/cotizacion_pdf', $data);

		}
		/*
			// Load pdf library
			$this->load->library('pdf');

			// Load HTML content
			$this->pdf->loadHtml($html);

			// (Optional) Setup the paper size and orientation
			$this->pdf->setPaper('A4', 'portrait');

			// Render the HTML as PDF
			$this->pdf->render();

			// Output the generated PDF (1 = download and 0 = preview)
			$this->pdf->stream("welcome.pdf", array("Attachment" => 0));
		*/

	}
	function get_price($_productos, $_cantidad, $_id) {
		$producto = $_productos[$_id];

		$cantidad_1 = $producto['cantidad_1'];
		$cantidad_2 = $producto['cantidad_2'];
		$cantidad_3 = $producto['cantidad_3'];
		$cantidad_4 = $producto['cantidad_4'];
		$cantidad_5 = $producto['cantidad_5'];

		$precio_1 = $producto['precio_1'];
		$precio_2 = $producto['precio_2'];
		$precio_3 = $producto['precio_3'];
		$precio_4 = $producto['precio_4'];
		$precio_5 = $producto['precio_5'];

		if ($this->_rango($cantidad_1, $cantidad_2, $_cantidad)) {
			return $precio_1;
		}

		if ($this->_rango($cantidad_2, $cantidad_3, $_cantidad)) {
			//echo $cantidad_2 . ' -- ' . $cantidad_3 . ' <-- ' . $_cantidad;
			//echo "<br>";
			return $precio_2;
		}

		if ($this->_rango($cantidad_3, $cantidad_4, $_cantidad)) {
			return $precio_3;
		}

		if ($this->_rango($cantidad_4, $cantidad_5, $_cantidad)) {
			return $precio_4;
		}

		if ($this->_rango($cantidad_5, 1000000000, $_cantidad)) {
			return $precio_5;
		}

	}
	function _rango($a, $b, $c) {
		if ($a <= $c && $c < $b) {
			return true;
		} else {
			return false;
		}

	}
	function ajax_save() {

		$_decimals = $this->config->item('decimals');

		/*
						cotizacion_id: 0

			: Cynthia Mueller
			: Serina Barton
			: on
			: Leila Reese
			: Lionel Ball
			: Ava Wilkins
			: Karly Chandler
			: Keegan Lawrence
			: Molly Sharp
			: Pearl Cochran
			: Britanney Potter
			: Avye Summers
			: pobude@mailinator.net
			: Kay Payne
			: Kim French
			: Justina Gutierrez

		*/

		$cotizacion_id = $this->input->post('cotizacion_id');
		$servicio_flete = $this->input->post('servicio_flete');
		$origen = $this->input->post('origen');
		$destino = $this->input->post('destino');
		$descripcion = $this->input->post('descripcion');
		$peso_embarque = $this->input->post('peso_embarque');
		$peso_embarque_dimension = $this->input->post('peso_embarque_dimension');


		$maniobras = $this->input->post('maniobras');
		$maniobras_importe = $this->input->post('maniobras_importe');

		$fecha_a = $this->input->post('fecha_a');
		$fecha_b = $this->input->post('fecha_b');
		$declarar_valor = $this->input->post('declarar_valor');
        $declarar_valor_val = $this->input->post('declarar_valor_val');
		$valor_no_declarado = $this->input->post('valor_no_declarado');
		$prima = $this->input->post('prima');
		$kilometros = $this->input->post('kilometros');
        $importe = $this->input->post('importe');
		$empresa = $this->input->post('empresa');

		$nombre = $this->input->post('nombre');
		$domicilio = $this->input->post('domicilio');
		$email = $this->input->post('email');
		$telefono = $this->input->post('telefono');
		$INE = $this->input->post('INE');
		$imagen = $this->input->post('imagen');
		$vehiculos_id = $this->input->post('vehiculos_id');

        $custody_importe = $this->input->post('custody_importe');

		$in['servicio_flete'] = $servicio_flete;
		$in['custody_importe'] = $custody_importe;
        $in['origen'] = $origen;
		$in['destino'] = $destino;
		$in['descripcion'] = $descripcion;
		$in['peso_embarque'] = $peso_embarque;
		$in['peso_embarque_dimension'] = $peso_embarque_dimension;

		$in['importe'] = $importe;
		$in['maniobras'] = $maniobras;
		$in['maniobras_importe'] = $maniobras_importe;

		$in['fecha_a'] = $fecha_a;
		$in['fecha_b'] = $fecha_b;
		$in['declarar_valor'] = $declarar_valor;
        $in['declarar_valor_val'] = $declarar_valor_val;
		$in['prima'] = $prima;
		$in['kilometros'] = $kilometros;
		$in['empresa'] = $empresa;

		$in['nombre'] = $nombre;
		$in['domicilio'] = $domicilio;
		$in['email'] = $email;
		$in['telefono'] = $telefono;
		$in['INE'] = $INE;
		$in['imagen'] = $imagen;
		$in['vehiculos_id'] = $vehiculos_id;
		$in['valor_no_declarado']=$valor_no_declarado;

		/* calculalndo el precio del prodycto*/

		//_json_error("No se han agregado  productos.");

		/**/
		
		/**/
		if ($cotizacion_id > 0) {
			// actualizar
			_cotizaciones($cotizacion_id);
			$in = _modified($in);
			$this->db->where('cotizacion_id', $cotizacion_id)->update("ce_cotizacion", $in);

		} else {

			/*creando la cotizacion*/
			$in = _created($in);

			$this->db->insert("ce_cotizacion", $in);
			$cotizacion_id = $this->db->insert_id();
		}


        $r['error'] = 0;
        $r['cotizacion_id'] = $cotizacion_id;
        $r['message'] = 'Se creo correctamente la cotizacion.';
        $this->session->set_userdata('cotizacion_id', $cotizacion_id);
//		_json($r);
	}
	function get_clientes() {
		$empresas_id = $this->input->post('empresas_id');
		$clientes = $this->db->select('B.ruc,B.nombre as empresa_nombre,A.*')->where('A.empresas_id', $empresas_id)->from('ce_clientes  A ')->join('ce_empresas B', 'A.empresas_id=B.empresas_id')->get()->result_array();

		$data['clientes'] = $clientes;
		$html = $this->load->view('cotizacion/get_clientes', $data, true);

		$data['total'] = count($clientes);
		$data['html'] = $html;

		echo json_encode($data);
	}

	function get_cliente() {
		$clientes_id = $this->input->post('clientes_id');
		$cliente = $this->db->select('B.ruc,B.marca,B.direccion,B.nombre as empresa_nombre,A.*')->where('A.clientes_id', $clientes_id)->from('ce_clientes  A ')->join('ce_empresas B', 'A.empresas_id=B.empresas_id')->get()->result_array();

		$data['cliente'] = $cliente;
		$html = $this->load->view('cotizacion/get_cliente', $data, true);

		$data['total'] = count($cliente);
		$data['html'] = $html;

		echo json_encode($data);
	}
	function ajax_list() {
		/*

			{
			  "current": 1,
			  "rowCount": 10,
			  "rows": [
			    {
			      "id": 19,
			      "sender": "123@test.de",
			      "received": "2014-05-30T22:15:00"
			    },
			    {
			      "id": 14,
			      "sender": "123@test.de",
			      "received": "2014-05-30T20:15:00"
			    },
			    ...
			  ],
			  "total": 1123
			}
		*/
/*current=1&rowCount=10&sort[sender]=asc&searchPhrase=&id=b0df282a-0d67-40e5-8558-c9e93b7befed*/
		$current = $this->input->post('current');
		$rowCount = $this->input->post('rowCount');
		if (_is_admin() || _is_lower_admin()) {

		} else {
			$this->db->where('X.user_created', _id());
		}
		$this->db->select("X.*, concat(Z.first_name,' ',Z.last_name) as autor")
			->from("ce_cotizacion X")
		//->join("ce_tutores Y", "Y.id=X.tutores_id")
			->join("users Z", "X.user_created=Z.ID", "left")
			->order_by('X.cotizacion_id', 'desc');
		$_temp_num = $total = $this->db->count_all_results('', false); //$this->db->get()->num_rows();
		$rows = $this->db->limit($rowCount, ($current - 1) * $rowCount)->get()->result_array();

		$response['rows'] = $rows;
		$response['current'] = $current;
		$response['rowCount'] = $rowCount;
		$response['total'] = $_temp_num; // count($rows);

		//$response = array();
		echo json_encode($response);
		exit();

	}

	function cambiar_status($cotizacion_id, $operacion = 0) {

		_cotizaciones($cotizacion_id, false);

		$this->cotizacion_model->change_status($cotizacion_id, $operacion);

		redirect(site_url('cotizacion/operacion/' . $cotizacion_id));
		//_helper_back();
	}
}

