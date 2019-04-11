<?php

namespace Intranet\Http\Controllers;

use Intranet\Botones\BotonIcon;
use Intranet\Botones\BotonImg;
use Intranet\Botones\BotonBasico;
use Intranet\Entities\Colaboracion;
use Illuminate\Support\Facades\Session;
use Mail;
use Intranet\Mail\DocumentRequest;
use Styde\Html\Facades\Alert;


/**
 * Class PanelColaboracionController
 * @package Intranet\Http\Controllers
 */
class PanelColaboracionController extends IntranetController
{

    /**
     * @var array
     */
    protected $gridFields = ['Empresa','concierto','Localidad','puestos','Xestado','contacto', 'telefono','email'];
    /**
     * @var string
     */
    protected $perfil = 'profesor';
    /**
     * @var string
     */
    protected $model = 'Colaboracion';


    /**
     * @return mixed
     */
    public function index()
    {
        $todos = $this->search();

        $this->crea_pestanas(config('modelos.'.$this->model.'.estados'),"profile.".strtolower($this->model),1,1);
        $this->iniBotones();
        Session::put('redirect','PanelColaboracionController@index');
        return $this->grid($todos);
    }


    /**
     *
     */
    protected function iniBotones()
    {
        $this->panel->setBoton('profile',new BotonIcon('colaboracion.unauthorize', ['roles' => config('roles.rol.practicas'),'class'=>'btn-primary unauthorize','img'=>'fa-question','where' => ['estado', '==', '2']]));
        $this->panel->setBoton('profile',new BotonIcon('colaboracion.resolve', ['roles' => config('roles.rol.practicas'),'class'=>'btn-success resolve','img'=>'fa-hand-o-up','where' => ['estado', '<>', '2']]));
        $this->panel->setBoton('profile',new BotonIcon('colaboracion.refuse', ['roles' => config('roles.rol.practicas'),'class'=>'btn-danger refuse','img'=>'fa-hand-o-down','where' => ['estado', '<', '3']]));
        $this->panel->setBothBoton('colaboracion.show',['img' => 'fa-eye','text' => '','roles' => [config('roles.rol.practicas')]]);
        $this->panel->setBoton('profile',new BotonIcon('colaboracion.contacto', ['roles' => config('roles.rol.practicas'),'img'=>'fa-envelope','where' => ['estado', '==', '1']]));
        $this->panel->setBoton('profile',new BotonIcon('colaboracion.documentacion', ['roles' => config('roles.rol.practicas'),'img'=>'fa-envelope','where' => ['estado', '==', '2']]));

        if (Colaboracion::where('estado', '=', 3)->count())
            $this->panel->setBoton('index', new BotonBasico("colaboracion.inicia",['icon' => 'fa fa-recycle']));
        if (Colaboracion::where('estado', '=', 1)->count())
            $this->panel->setBoton('index', new BotonBasico("colaboracion.contacto",['icon' => 'fa fa-envelope']));
        if (Colaboracion::where('estado', '=', 2)->count())
            $this->panel->setBoton('index', new BotonBasico("colaboracion.info",['icon' => 'fa fa-envelope-o']));
        if (Colaboracion::where('estado', '=', 2)->count())
            $this->panel->setBoton('index', new BotonBasico("colaboracion.documentacion",['icon' => 'fa fa-envelope-o']));

    }

    /**
     * @return mixed
     */
    public function search(){
        $this->titulo = ['quien' => Colaboracion::MiColaboracion()->first()->Ciclo->literal];
        return Colaboracion::MiColaboracion()->get();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function inicia(){
        Colaboracion::MiColaboracion()->update(['estado' => 1]);
        return $this->redirect();
    }


    public function sendRequestInfo($id=null){
        $colaboraciones = $id?Colaboracion::where('id',$id)->get():Colaboracion::MiColaboracion()->where('estado',2)->get();
        foreach ($colaboraciones as $colaboracion)
            $this->emailDocument('request',$colaboracion);
        return back();
    }

    public function sendFirstContact($id=null){
        $colaboraciones = $id?Colaboracion::where('id',$id)->get():Colaboracion::MiColaboracion()->where('estado',1)->get();
        foreach ($colaboraciones as $colaboracion)
            $this->emailDocument('contact',$colaboracion);
        return back();
    }
    public function sendDocumentation($id=null){
        $colaboraciones = $id?Colaboracion::where('id',$id)->get():Colaboracion::MiColaboracion()->where('estado',2)->get();
        foreach ($colaboraciones as $colaboracion)
            $this->emailDocument('documentation',$colaboracion);
        return back();
    }


    /**
     * @param $document
     * @param $colaboracion
     */
    public function emailDocument($document, $colaboracion){
        // TODO : canviar AuthUser()->email per correu instructor
        Mail::to(AuthUser()->email, AuthUser()->ShortName)
            ->send(new DocumentRequest($colaboracion, AuthUser()->email
                ,config('fctEmails.'.$document.'.subject')
                ,config('fctEmails.'.$document.'.view')));
        Alert::info('Enviat correu '.config('fctEmails.'.$document.'.subject').' a '.$colaboracion->Centro->nombre);
    }


}
