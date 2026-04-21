import React from 'react';
import { useNavigate, Outlet, useLocation } from 'react-router';
import { useAppContext } from '../context/AppContext';
import { 
  LayoutDashboard, 
  Users, 
  Calendar, 
  ClipboardList, 
  CreditCard, 
  LogOut,
  Bell,
  Search
} from 'lucide-react';
import { Card } from './ui/shared';
import { siteContent } from '../config/siteContent';

export const Dashboard = () => {
  const { user, logout, appointments, patients } = useAppContext();
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const stats = [
    { label: 'Total pacientes', value: patients.length, icon: <Users />, color: 'bg-blue-500' },
    { label: 'Citas del día', value: appointments.filter(a => a.date === '2026-02-21').length, icon: <Calendar />, color: 'bg-[var(--brand-primary)]' },
    { label: 'Próximas citas', value: appointments.filter(a => a.status === 'pending').length, icon: <ClipboardList />, color: 'bg-yellow-500' },
    { label: 'Pendientes de pago', value: 3, icon: <CreditCard />, color: 'bg-red-500' },
  ];

  const menuItems = [
    { label: 'Inicio', icon: <LayoutDashboard size={20} />, path: '/admin' },
    { label: 'Pacientes', icon: <Users size={20} />, path: '/admin/patients' },
    { label: 'Citas', icon: <Calendar size={20} />, path: '/admin/appointments' },
    { label: 'Consultas', icon: <ClipboardList size={20} />, path: '/admin/consultas' },
  ];

  const isHome = location.pathname === '/admin';

  return (
    <div className="flex h-screen overflow-hidden bg-[var(--brand-soft)]">
      {/* Sidebar */}
      <aside className="w-64 bg-white border-r border-gray-100 flex flex-col">
        <div className="p-8">
          <h1 className="font-display text-4xl leading-none text-[var(--brand-primary)]">{siteContent.brand.name}</h1>
        </div>
        
        <nav className="flex-1 px-4 space-y-2">
          {menuItems.map((item) => (
            <button
              key={item.label}
              onClick={() => navigate(item.path)}
              className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
                location.pathname === item.path 
                  ? 'bg-[var(--brand-soft)] text-[var(--brand-primary)] font-semibold' 
                  : 'text-gray-500 hover:bg-gray-50'
              }`}
            >
              {item.icon}
              {item.label}
            </button>
          ))}
        </nav>

        <div className="p-4 border-t border-gray-50">
          <button 
            onClick={handleLogout}
            className="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50"
          >
            <LogOut size={20} />
            Cerrar sesión
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 flex flex-col overflow-hidden">
        {/* Top Header */}
        <header className="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-8">
          <div className="relative w-96">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
            <input 
              className="w-full rounded-lg border border-[var(--brand-border)] bg-white py-2 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-black/5"
              placeholder="Buscar pacientes, citas..."
            />
          </div>
          <div className="flex items-center gap-6">
            <button className="relative p-2 text-gray-400 hover:text-[var(--brand-primary)]">
              <Bell size={20} />
              <span className="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white" />
            </button>
            <div className="flex items-center gap-3">
              <div className="text-right">
                <p className="text-sm font-semibold">{user?.name}</p>
                <p className="text-xs text-gray-400">Odontólogo</p>
              </div>
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--brand-primary)] text-white font-bold">
                D32
              </div>
            </div>
          </div>
        </header>

        {/* Scrollable Content Area */}
        <div className="flex-1 overflow-y-auto p-8">
          {isHome ? (
            <div className="space-y-8">
              <div>
                <h2 className="text-2xl font-bold text-gray-900">Dashboard</h2>
                <p className="text-gray-500">Resumen general de la clínica para hoy</p>
              </div>

              {/* Stats Cards */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {stats.map((stat) => (
                  <Card key={stat.label} className="flex items-center gap-4 border-none shadow-sm">
                    <div className={`${stat.color} w-12 h-12 rounded-2xl flex items-center justify-center text-white`}>
                      {React.cloneElement(stat.icon as React.ReactElement, { size: 24 })}
                    </div>
                    <div>
                      <p className="text-gray-500 text-sm">{stat.label}</p>
                      <p className="text-2xl font-bold">{stat.value}</p>
                    </div>
                  </Card>
                ))}
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Recent Appointments */}
                <div className="lg:col-span-2">
                   <Card className="h-full">
                      <div className="flex justify-between items-center mb-6">
                        <h3 className="text-lg font-bold">Citas Recientes</h3>
                        <button onClick={() => navigate('/admin/appointments')} className="text-sm font-semibold text-[var(--brand-primary)] hover:underline">Ver todas</button>
                      </div>
                      <div className="space-y-4">
                        {appointments.slice(0, 5).map((app) => (
                          <div key={app.id} className="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50 transition-colors">
                            <div className="flex items-center gap-4">
                              <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500">
                                {app.patientName.charAt(0)}
                              </div>
                              <div>
                                <p className="font-semibold">{app.patientName}</p>
                                <p className="text-xs text-gray-400">{app.type} • {app.time}</p>
                              </div>
                            </div>
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                              app.status === 'confirmed' ? 'bg-green-100 text-green-600' : 
                              app.status === 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600'
                            }`}>
                              {app.status === 'confirmed' ? 'Confirmada' : app.status === 'pending' ? 'Pendiente' : 'Cancelada'}
                            </span>
                          </div>
                        ))}
                      </div>
                   </Card>
                </div>
                
                {/* Quick Info */}
                <div className="space-y-8">
                  <Card>
                    <h3 className="text-lg font-bold mb-4">Avisos</h3>
                    <div className="space-y-4">
                       <div className="p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                          <p className="text-sm text-yellow-800 font-medium">Insumos bajos</p>
                          <p className="text-xs text-yellow-600">Revisar stock de guantes y anestesia.</p>
                       </div>
                       <div className="p-3 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                          <p className="text-sm text-blue-800 font-medium">Mantenimiento</p>
                          <p className="text-xs text-blue-600">Silla 2 programada para revisión mañana.</p>
                       </div>
                    </div>
                  </Card>
                </div>
              </div>
            </div>
          ) : (
            <Outlet />
          )}
        </div>
      </main>
    </div>
  );
};
