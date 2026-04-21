import React, { useState } from 'react';
import { useAppContext } from '../context/AppContext';
import { Button, Input, Card } from './ui/shared';
import { Plus, Search, MoreVertical, UserPlus, X } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { toast } from 'sonner';

export const PatientsList = () => {
  const { patients, addPatient } = useAppContext();
  const [showForm, setShowForm] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  // Form State
  const [formData, setFormData] = useState({
    name: '',
    dpi: '',
    birthDate: '',
    phone: '',
    email: '',
    address: '',
    allergies: '',
    conditions: '',
    medications: ''
  });

  const filteredPatients = patients.filter(p => 
    p.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
    p.dpi.includes(searchTerm)
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name || !formData.dpi || !formData.email) {
      toast.error('Por favor completa los campos obligatorios');
      return;
    }
    
    addPatient(formData);
    toast.success('Paciente registrado exitosamente');
    setShowForm(false);
    setFormData({
      name: '', dpi: '', birthDate: '', phone: '', email: '', address: '',
      allergies: '', conditions: '', medications: ''
    });
  };

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Gestión de Pacientes</h2>
          <p className="text-gray-500">Administra la base de datos de tus pacientes</p>
        </div>
        <Button className="flex items-center gap-2" onClick={() => setShowForm(true)}>
          <UserPlus size={18} />
          Registrar paciente
        </Button>
      </div>

      <Card className="border-none shadow-sm overflow-hidden">
        <div className="p-4 border-b border-gray-100 flex items-center justify-between bg-white">
          <div className="relative w-80">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
            <input 
              className="w-full rounded-lg border border-[var(--brand-border)] bg-white py-2 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-black/5"
              placeholder="Buscar por nombre o DPI..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
              <tr>
                <th className="px-6 py-4">Nombre</th>
                <th className="px-6 py-4">DPI</th>
                <th className="px-6 py-4">Teléfono</th>
                <th className="px-6 py-4">Correo</th>
                <th className="px-6 py-4 text-center">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {filteredPatients.map((patient) => (
                <tr key={patient.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--brand-soft)] text-[var(--brand-primary)] font-bold text-xs">
                        {patient.name.charAt(0)}
                      </div>
                      <span className="font-medium text-gray-900">{patient.name}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 text-gray-500 text-sm">{patient.dpi}</td>
                  <td className="px-6 py-4 text-gray-500 text-sm">{patient.phone}</td>
                  <td className="px-6 py-4 text-gray-500 text-sm">{patient.email}</td>
                  <td className="px-6 py-4 text-center">
                    <button className="text-gray-400 hover:text-[var(--brand-primary)]">
                      <MoreVertical size={18} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>

      {/* Registration Modal */}
      <AnimatePresence>
        {showForm && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="absolute inset-0 bg-black/40 backdrop-blur-sm"
              onClick={() => setShowForm(false)}
            />
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              className="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            >
              <div className="p-6 border-b flex justify-between items-center bg-white sticky top-0 z-10">
                <div>
                  <h2 className="text-xl font-bold">Registrar Nuevo Paciente</h2>
                  <p className="text-sm text-gray-500">Completa la ficha clínica del paciente</p>
                </div>
                <button onClick={() => setShowForm(false)} className="p-2 hover:bg-gray-100 rounded-full transition-colors">
                  <X size={20} className="text-gray-400" />
                </button>
              </div>

              <form onSubmit={handleSubmit} className="p-8 overflow-y-auto space-y-8">
                {/* Personal Info */}
                <div>
                  <h3 className="mb-6 border-b border-[var(--brand-border)] pb-2 text-sm font-bold uppercase tracking-wider text-[var(--brand-primary)]">Información personal</h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Input label="Nombre completo *" placeholder="Nombre Apellido" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
                    <Input label="DPI / Identificación *" placeholder="0000 00000 0000" value={formData.dpi} onChange={e => setFormData({...formData, dpi: e.target.value})} />
                    <Input label="Fecha de nacimiento" type="date" value={formData.birthDate} onChange={e => setFormData({...formData, birthDate: e.target.value})} />
                    <Input label="Teléfono" placeholder="5555-5555" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} />
                    <Input label="Correo electrónico *" placeholder="correo@ejemplo.com" type="email" value={formData.email} onChange={e => setFormData({...formData, email: e.target.value})} />
                    <Input label="Dirección" placeholder="Ciudad, Zona..." value={formData.address} onChange={e => setFormData({...formData, address: e.target.value})} />
                  </div>
                </div>

                {/* Medical Info */}
                <div>
                  <h3 className="text-sm font-bold text-red-500 uppercase tracking-wider mb-6 pb-2 border-b border-red-500/10">Información Médica</h3>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="flex flex-col gap-1.5">
                      <label className="text-sm font-medium text-gray-500">Alergias</label>
                      <textarea 
                        className="min-h-[100px] w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                        placeholder="Ej. Penicilina, látex..."
                        value={formData.allergies}
                        onChange={e => setFormData({...formData, allergies: e.target.value})}
                      />
                    </div>
                    <div className="flex flex-col gap-1.5">
                      <label className="text-sm font-medium text-gray-500">Enfermedades preexistentes</label>
                      <textarea 
                        className="min-h-[100px] w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                        placeholder="Ej. Diabetes, hipertensión..."
                        value={formData.conditions}
                        onChange={e => setFormData({...formData, conditions: e.target.value})}
                      />
                    </div>
                    <div className="flex flex-col gap-1.5">
                      <label className="text-sm font-medium text-gray-500">Medicamentos actuales</label>
                      <textarea 
                        className="min-h-[100px] w-full rounded-[14px] border border-[var(--brand-border)] bg-white px-4 py-3 outline-none focus:border-[var(--brand-primary)]"
                        placeholder="Listado de medicamentos..."
                        value={formData.medications}
                        onChange={e => setFormData({...formData, medications: e.target.value})}
                      />
                    </div>
                  </div>
                </div>
              </form>

              <div className="p-6 bg-gray-50 border-t flex justify-end gap-4 sticky bottom-0 z-10">
                <Button variant="outline" onClick={() => setShowForm(false)}>Cancelar</Button>
                <Button onClick={handleSubmit}>Guardar Paciente</Button>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
};
