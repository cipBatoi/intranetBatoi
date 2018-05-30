<?php

namespace Intranet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Intranet\Events\ActivityReport;

class Modulo extends Model
{

    public $primaryKey = 'codigo';
    public $timestamps = false;
    public $keyType = 'string';

    use BatoiModels;

    protected $fillable = [
        'cliteral',
        'vliteral',
        'idCiclo',
        'departamento'
    ];
    protected $rules = [
        'cliteral' => 'required',
        'vliteral' => 'required',
    ];
    protected $inputTypes = [
        'idCiclo' => ['type' => 'select'],
        'departamento' => ['type' => 'select']
    ];
    protected $dispatchesEvents = [
        'saved' => ActivityReport::class,
        'deleted' => ActivityReport::class,
    ];

    public function getIdCicloOptions()
    {
        return hazArray(Ciclo::all(), 'id', 'ciclo');
    }
    public function getDepartamentoOptions()
    {
        return hazArray(Departamento::all(), 'id', 'literal');
    }

    public function Horario()
    {
        return $this->hasMany(Horario::class, 'codigo', 'modulo');
    }



    public function Ciclo()
    {
        return $this->belongsto(Ciclo::class, 'idCiclo', 'id');
    }
    public function Departament()
    {
        return $this->belongsto(Departamento::class, 'departamento', 'id');
    }

    public function scopeDepartamento($query, $dep = null)
    {
        $dep = $dep ? $dep : AuthUser()->departamento;
        return $query->where('departamento', $dep);
    }

    public function scopeMisModulos($query, $profesor = null)
    {
        $profesor = $profesor ? $profesor : AuthUser();
        $modulos = Horario::select('modulo')
                ->Profesor(AuthUser()->dni)
                ->whereNotNull('idGrupo')
                ->distinct()
                ->get()
                ->toarray();
        return $query->whereIn('codigo', $modulos);
    }
    
    public function scopeModulosGrupo($query,$grupo){
        $modulos = Horario::select('modulo')
                ->Grup($grupo)
                ->distinct()
                ->get()
                ->toarray();
        return $query->whereIn('codigo', $modulos);
    }
    
    public function scopeLectivos($query)
    {
        return $query->whereNotIn('codigo',config('constants.modulosNoLectivos'));
    }
    public function getliteralAttribute()
    {
        return App::getLocale(session('lang')) == 'es' ? $this->cliteral : $this->vliteral;
    }
    public function getXcicloAttribute()
    {
        return isset($this->Ciclo->ciclo)?$this->Ciclo->ciclo:'';
    }
    public function getXDepartamentoAttribute()
    {
        return $this->Departament->literal;
    }


}
