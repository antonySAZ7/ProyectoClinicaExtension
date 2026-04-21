import React, { useState } from 'react';
import { useAppContext } from '../context/AppContext';
import { Card, Button } from './ui/shared';
import { Search, Filter, Check, X, Calendar as CalendarIcon, Clock, ChevronRight } from 'lucide-react';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { toast } from 'sonner';

export const AppointmentsList = () => {
  const { appointments, updateAppointmentStatus } = useAppContext();
  const [searchTerm, setSearchTerm] = useState('');

  const filteredAppointments = appointments.filter(app => 
    app.patientName.toLowerCase().includes(searchTerm.toLowerCase()) || 
    app.type.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleStatusUpdate = (id: string, status: 'confirmed' | 'cancelled') => {
    updateAppointmentStatus(id, status);
    toast.success(`Cita ${status === 'confirmed' ? 'confirmada' : 'cancelada'} exitosamente`);
  };

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Gestión de Citas</h2>
          <p className="text-gray-500">Administra las citas programadas de la clínica</p>
        </div>
        <div className="flex gap-4">
          <Button variant="outline" className="flex items-center gap-2">
            <Filter size={18} />
            Filtrar
          </Button>
          <Button className="flex items-center gap-2">
            <CalendarIcon size={18} />
            Nueva Cita
          </Button>
        </div>
      </div>

      <Card className="border-none shadow-sm overflow-hidden">
        <div className="p-4 border-b border-gray-100 flex items-center justify-between bg-white">
          <div className="relative w-80">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
            <input 
              className="w-full rounded-lg border border-[var(--brand-border)] bg-white py-2 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-black/5"
              placeholder="Buscar por paciente o tipo..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
              <tr>
                <th className="px-6 py-4">Paciente</th>
                <th className="px-6 py-4">Tipo de Cita</th>
                <th className="px-6 py-4">Fecha</th>
                <th className="px-6 py-4">Hora</th>
                <th className="px-6 py-4 text-center">Estado</th>
                <th className="px-6 py-4 text-center">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {filteredAppointments.map((app) => (
                <tr key={app.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <span className="font-medium text-gray-900">{app.patientName}</span>
                  </td>
                  <td className="px-6 py-4">
                    <span className="px-2.5 py-1 rounded-md bg-gray-100 text-gray-600 text-xs font-medium">
                      {app.type}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-gray-500 text-sm">
                    {format(new Date(app.date), 'dd/MM/yyyy')}
                  </td>
                  <td className="px-6 py-4 text-gray-500 text-sm">
                    {app.time}
                  </td>
                  <td className="px-6 py-4 text-center">
                    <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                      app.status === 'confirmed' ? 'bg-green-100 text-green-600' : 
                      app.status === 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600'
                    }`}>
                      {app.status === 'confirmed' ? 'Confirmada' : app.status === 'pending' ? 'Pendiente' : 'Cancelada'}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex justify-center gap-2">
                      {app.status === 'pending' && (
                        <>
                          <button 
                            onClick={() => handleStatusUpdate(app.id, 'confirmed')}
                            className="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors"
                            title="Confirmar"
                          >
                            <Check size={18} />
                          </button>
                          <button 
                            onClick={() => handleStatusUpdate(app.id, 'cancelled')}
                            className="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                            title="Cancelar"
                          >
                            <X size={18} />
                          </button>
                        </>
                      )}
                      <button className="p-1.5 rounded-lg bg-gray-50 text-gray-400 hover:bg-gray-100 transition-colors">
                        <ChevronRight size={18} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {filteredAppointments.length === 0 && (
            <div className="py-20 text-center text-gray-500">
              No se encontraron citas que coincidan con la búsqueda.
            </div>
          )}
        </div>
      </Card>
    </div>
  );
};
