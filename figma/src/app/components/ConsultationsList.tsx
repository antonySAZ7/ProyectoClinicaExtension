import React, { useState } from 'react';
import { useAppContext } from '../context/AppContext';
import { Card, Button, Input } from './ui/shared';
import { Search, Plus, X, Trash2 } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { toast } from 'sonner';

export const ConsultationsList = () => {
  const { patients, consultations, addConsultation } = useAppContext();

  const [showForm, setShowForm] = useState(false);
  const [selectedPatient, setSelectedPatient] = useState('');

  const [formData, setFormData] = useState({
    date: '',
    reason: '',
    weight: '',
    height: '',
    bloodPressure: '',
    heartRate: '',
    respiratoryRate: '',
    observations: ''
  });

  const [treatments, setTreatments] = useState<any[]>([]);
  const [treatmentForm, setTreatmentForm] = useState({
    tooth: '',
    diagnosis: '',
    procedure: '',
    price: ''
  });

  const handleAddTreatment = () => {
    if (!treatmentForm.tooth || !treatmentForm.procedure) {
      toast.error('Completa los campos del tratamiento');
      return;
    }

    setTreatments([...treatments, { ...treatmentForm, id: Date.now() }]);
    setTreatmentForm({ tooth: '', diagnosis: '', procedure: '', price: '' });
  };

  const handleRemoveTreatment = (id: number) => {
    setTreatments(treatments.filter(t => t.id !== id));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!selectedPatient || !formData.date || !formData.reason) {
      toast.error('Completa los campos obligatorios');
      return;
    }

    addConsultation({
      patientId: selectedPatient,
      ...formData,
      treatments
    });

    toast.success('Consulta registrada exitosamente');
    setShowForm(false);
    setFormData({
      date: '',
      reason: '',
      weight: '',
      height: '',
      bloodPressure: '',
      heartRate: '',
      respiratoryRate: '',
      observations: ''
    });
    setTreatments([]);
  };

  return (
    <div className="space-y-8">

      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Gestión de Consultas</h2>
          <p className="text-gray-500">Administra las consultas clínicas registradas</p>
        </div>
        <Button onClick={() => setShowForm(true)} className="flex items-center gap-2">
          <Plus size={18} />
          Nueva Consulta
        </Button>
      </div>

      <Card className="p-6">
        {consultations?.length === 0 ? (
          <p className="text-gray-500 text-center py-10">
            No hay consultas registradas.
          </p>
        ) : (
          <div className="space-y-4">
            {consultations.map((c: any) => (
              <div key={c.id} className="p-4 border rounded-xl bg-gray-50">
                <p className="font-semibold">{c.reason}</p>
                <p className="text-sm text-gray-500">{c.date}</p>
                <p className="text-xs text-gray-400">
                  {c.treatments?.length} tratamientos registrados
                </p>
              </div>
            ))}
          </div>
        )}
      </Card>

      {/* Modal */}
      <AnimatePresence>
        {showForm && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <motion.div
              className="absolute inset-0 bg-black/40"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setShowForm(false)}
            />
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              className="relative w-full max-w-5xl bg-white rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh]"
            >
              <div className="p-6 border-b flex justify-between items-center">
                <h2 className="text-xl font-bold">Nueva Consulta</h2>
                <button onClick={() => setShowForm(false)}>
                  <X size={20} />
                </button>
              </div>

              <form onSubmit={handleSubmit} className="p-8 space-y-8">

                {/* Selección Paciente */}
                <div>
                  <label className="text-sm font-medium text-gray-500">Paciente *</label>
                  <select
                    className="w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                    value={selectedPatient}
                    onChange={e => setSelectedPatient(e.target.value)}
                  >
                    <option value="">Seleccionar paciente</option>
                    {patients.map((p: any) => (
                      <option key={p.id} value={p.id}>{p.name}</option>
                    ))}
                  </select>
                </div>

                {/* Datos Consulta */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <Input label="Fecha *" type="date" value={formData.date}
                    onChange={e => setFormData({...formData, date: e.target.value})} />
                  <Input label="Motivo consulta *" value={formData.reason}
                    onChange={e => setFormData({...formData, reason: e.target.value})} />
                  <Input label="Peso (kg)" value={formData.weight}
                    onChange={e => setFormData({...formData, weight: e.target.value})} />
                  <Input label="Altura (cm)" value={formData.height}
                    onChange={e => setFormData({...formData, height: e.target.value})} />
                  <Input label="Presión arterial" value={formData.bloodPressure}
                    onChange={e => setFormData({...formData, bloodPressure: e.target.value})} />
                  <Input label="Frecuencia cardíaca" value={formData.heartRate}
                    onChange={e => setFormData({...formData, heartRate: e.target.value})} />
                  <Input label="Frecuencia respiratoria" value={formData.respiratoryRate}
                    onChange={e => setFormData({...formData, respiratoryRate: e.target.value})} />
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-500">Observaciones</label>
                  <textarea
                    className="w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                    value={formData.observations}
                    onChange={e => setFormData({...formData, observations: e.target.value})}
                  />
                </div>

                {/* Tratamientos */}
                <div className="space-y-4">
                  <h3 className="font-bold text-[var(--brand-primary)]">Agregar tratamiento</h3>
                  <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Input label="Pieza Dental"
                      value={treatmentForm.tooth}
                      onChange={e => setTreatmentForm({...treatmentForm, tooth: e.target.value})} />
                    <Input label="Diagnóstico"
                      value={treatmentForm.diagnosis}
                      onChange={e => setTreatmentForm({...treatmentForm, diagnosis: e.target.value})} />
                    <Input label="Procedimiento"
                      value={treatmentForm.procedure}
                      onChange={e => setTreatmentForm({...treatmentForm, procedure: e.target.value})} />
                    <Input label="Precio"
                      value={treatmentForm.price}
                      onChange={e => setTreatmentForm({...treatmentForm, price: e.target.value})} />
                  </div>

                  <Button type="button" onClick={handleAddTreatment}>
                    Agregar tratamiento
                  </Button>

                  {treatments.length > 0 && (
                    <div className="mt-4 space-y-2">
                      {treatments.map(t => (
                        <div key={t.id} className="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                          <div>
                            <p className="font-medium">{t.procedure}</p>
                            <p className="text-xs text-gray-500">Pieza: {t.tooth} | Q{t.price}</p>
                          </div>
                          <button onClick={() => handleRemoveTreatment(t.id)}>
                            <Trash2 size={16} className="text-red-500" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                <div className="flex justify-end gap-4">
                  <Button variant="outline" onClick={() => setShowForm(false)}>
                    Cancelar
                  </Button>
                  <Button type="submit">
                    Guardar Consulta
                  </Button>
                </div>

              </form>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

    </div>
  );
};
