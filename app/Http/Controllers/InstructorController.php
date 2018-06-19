<?php

namespace Intranet\Http\Controllers;

use Illuminate\Http\Request;
use Intranet\Entities\Colaboracion;
use Intranet\Entities\Instructor;
use Intranet\Entities\Centro;
use Intranet\Entities\Centro_instructor;
use Intranet\Entities\Ciclo;
use Intranet\Entities\Empresa;
use Intranet\Entities\Fct;
use Intranet\Entities\Profesor;
use Response;
use Exception;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Styde\Html\Facades\Alert;
use Illuminate\Support\Facades\Session;
use Intranet\Botones\BotonImg;
use Jenssegers\Date\Date;


class InstructorController extends IntranetController
{

    protected $perfil = 'profesor';
    protected $model = 'Instructor';
    protected $titulo = [];
    protected $gridFields = ['dni', 'nombre','email','Nfcts', 'TutoresFct','Xcentros','telefono'];
    protected $modal = false;
    
    use traitImprimir;
    
    public function iniBotones()
    {
        $this->panel->setBoton('grid', new BotonImg('instructor.edit'));
        $this->panel->setBoton('grid', new BotonImg('instructor.show'));
        $this->panel->setBoton('grid', new BotonImg('instructor.pdf'));
    }
    
    public function search()
    {
        $fcts = Fct::misFcts()->get();
        $instructores = [];
        foreach ($fcts as $fct){
            foreach ($fct->Instructores as $instructor){
                $instructores[] = $instructor->dni;
            }
        }    
        return Instructor::whereIn('dni',$instructores)->get();
    }
    
    public function show($id)
    {
        $empresa = Instructor::find($id)->Centros->first()->idEmpresa;
        return redirect("empresa/$empresa/detalle");
    }

    private function nouCentreInstructor($centro, $dni)
    {
        $ci = Centro_instructor::where('idInstructor', $dni)->where('idCentro', $centro)->first();
        if (!$ci) {
            $ci = new Centro_instructor();
            $ci->idCentro = $centro;
            $ci->idInstructor = $dni;
            $ci->save();
        }
    }

    public function crea($centro)
    {
        return parent::create();
    }
    
    public function edita($id,$empresa)
    {
        return parent::edit($id);
    }
    
    public function guarda(Request $request, $id,$centro)
    {
        parent::update($request, $id);
        Session::put('pestana',2);
        return redirect()->action('EmpresaController@show', ['id' => Centro::find($centro)->idEmpresa]);
    }
    
    public function almacena(Request $request,$centro)
    {
        DB::transaction(function() use ($request,$centro) {
            $instructor = Instructor::find($request->dni);
            if (!$instructor){
                if (!$request->dni) {
                     $max = Instructor::where('dni','>', 'EU0000000')->where('dni','<','EU9999999')->max('dni');
                     $max = (int) substr($max, 2) +1;
                     $dni = 'EU'.str_pad($max, 7,'0', STR_PAD_LEFT);
                     $request->merge(['dni' => $dni]);
                }
                parent::store($request);
            }
            $this->nouCentreInstructor($centro, $request->dni);
        });
        Session::put('pestana',2);
        return redirect()->action('EmpresaController@show', ['id' => Centro::find($centro)->idEmpresa]);
    }

    public function delete($id,$centro)
    {
        $instructor = Instructor::find($id);
        $instructor->Centros()->detach($centro);
        if (Centro_instructor::where('idInstructor', $id)->count() == 0)
            parent::destroy($id);
        Session::put('pestana',2);
        return redirect()->action('EmpresaController@show', ['id' => Centro::find($centro)->idEmpresa]);
    }
    public function copy($id,$idCentro)
    {
        $instructor = Instructor::findOrFail($id);
        $centro = Centro::findOrFail($idCentro);
        $posibles = [];
        foreach ($centro->Empresa->centros as $centre){
            if (Centro_instructor::where('idCentro',$centre->id)->where('idInstructor',$id)->count()==0)
                $posibles[$centre->id] = $centre->nombre.'('.$centre->direccion.')';
        }
        return view('instructor.copy',compact('instructor','posibles','centro'));
    }
    public function toCopy(Request $request,$id,$idCentro)
    {
        $instructor = Instructor::findOrFail($id);
        $centro = Centro::findOrFail($idCentro);
        $instructor->Centros()->attach($request->centro);
        if ($request->accion == 'mou'){
            $instructor->Centros()->detach($idCentro);
        }
        Session::put('pestana',2);
        return redirect()->action('EmpresaController@show', ['id' => Centro::find($idCentro)->idEmpresa]);
    }
    
    public function pdf($id)
    {
        $instructor = Instructor::findOrFail($id);
        if ($instructor->surnames != ''){
            $fcts = $instructor->Fcts;
            $fecha = $this->ultima_fecha($fcts);
            $secretario = Profesor::find(config('contacto.secretario'));
            $director = Profesor::find(config('contacto.director'));
            $dades = ['date' => FechaString($fecha,'ca'),
                'fecha' => FechaString($fecha,'es'),
                'consideracion' => $secretario->sexo === 'H' ? 'En' : 'Na',
                'secretario' => $secretario->FullName,
                'centro' => config('contacto.nombre'),
                'poblacion' => config('contacto.poblacion'),
                'provincia' => config('contacto.provincia'),
                'director' => $director->FullName,
                'instructor' => $instructor
            ];
            if ($fcts->count()==1)
                $pdf = $this->hazPdf('pdf.fct.instructor', $fcts->first(), $dades);
            else
            {
                $centros = [];
                foreach ($fcts as $fct){
                    if (!in_array($fct->Colaboracion->idCentro, $centros))
                        $centros[] = $fct->Colaboracion->idCentro;
                }
                $pdf = $this->hazPdf('pdf.fct.instructors', $centros, $dades);
            }
            return $pdf->stream();
        }
        else
        {
            Alert::danger("Completa les dades de l'instructor");
            return redirect("/instructor");   
        }
        
    }
    private function ultima_fecha($fcts)
    {
        $posterior = new Date();
        foreach ($fcts as $fct){
            $posterior = FechaPosterior($fct->hasta, $posterior);
        }
        return $posterior;
    }


}
