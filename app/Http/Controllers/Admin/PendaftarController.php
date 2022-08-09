<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyPendaftarRequest;
use App\Http\Requests\StorePendaftarRequest;
use App\Http\Requests\UpdatePendaftarRequest;
use App\Models\Event;
use App\Models\Pendaftar;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PendaftarController extends Controller {
    use MediaUploadingTrait;
    use CsvImportTrait;

    function ekspor(){
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'user@example.com';                     //SMTP username
            $mail->Password   = 'secret';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
            $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');
        
            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function index() {
        abort_if ( Gate::denies( 'pendaftar_access' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $pendaftars = Pendaftar::with( [ 'event' ] )->get();

        $events = Event::get();

        return view( 'admin.pendaftars.index', compact( 'events', 'pendaftars' ) );
    }

    public function create() {
        abort_if ( Gate::denies( 'pendaftar_create' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $events = Event::pluck( 'nama_event', 'id' )->prepend( trans( 'global.pleaseSelect' ), '' );
        $no_t = Pendaftar::orderBy( 'no_tiket', 'DESC' )->first();
        return view( 'admin.pendaftars.create', compact( 'events', 'no_t' ) );
    }

    

    public function store( StorePendaftarRequest $request ) {
        $u = ( int )$request->input( 'total_bayar' );

        for ( $u = 0; $u<$request->input( 'total_bayar' );
        $u++ ) {
            $no_tiket = '0' . Pendaftar::orderBy( 'no_tiket', 'DESC' )->first()->no_tiket + 1;
            // $pendaftar->no_tiket = '0' . Pendaftar::latest()->first()->nama;
            $total_bayar = Event::find( $request->input( 'event_id' ) )->harga;
            $pendaftar = Pendaftar::create( array_merge( $request->all(), [
                'no_tiket' => $no_tiket,
                'total_bayar' => $total_bayar,
            ] ) );
            if ( $media = $request->input( 'ck-media', false ) ) {
                Media::whereIn( 'id', $media )->update( [ 'model_id' => $pendaftar->id ] );
            }

        }

        return redirect()->route( 'admin.pendaftars.index' );
    }

    public function edit( Pendaftar $pendaftar ) {
        abort_if ( Gate::denies( 'pendaftar_edit' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $events = Event::pluck( 'nama_event', 'id' )->prepend( trans( 'global.pleaseSelect' ), '' );

        $pendaftar->load( 'event' );

        return view( 'admin.pendaftars.edit', compact( 'events', 'pendaftar' ) );
    }

    public function update( UpdatePendaftarRequest $request, Pendaftar $pendaftar ) {
        $pendaftar->update( $request->all() );

        return redirect()->route( 'admin.pendaftars.index' );
    }

    public function show( Pendaftar $pendaftar ) {
        abort_if ( Gate::denies( 'pendaftar_show' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $pendaftar->load( 'event' );

        return view( 'admin.pendaftars.show', compact( 'pendaftar' ) );
    }

    public function destroy( Pendaftar $pendaftar ) {
        abort_if ( Gate::denies( 'pendaftar_delete' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $pendaftar->delete();

        return back();
    }

    public function massDestroy( MassDestroyPendaftarRequest $request ) {
        Pendaftar::whereIn( 'id', request( 'ids' ) )->delete();

        return response( null, Response::HTTP_NO_CONTENT );
    }

    public function storeCKEditorImages( Request $request ) {
        abort_if ( Gate::denies( 'pendaftar_create' ) && Gate::denies( 'pendaftar_edit' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        $model         = new Pendaftar();
        $model->id     = $request->input( 'crud_id', 0 );
        $model->exists = true;
        $media         = $model->addMediaFromRequest( 'upload' )->toMediaCollection( 'ck-media' );

        return response()->json( [ 'id' => $media->id, 'url' => $media->getUrl() ], Response::HTTP_CREATED );
    }
}
