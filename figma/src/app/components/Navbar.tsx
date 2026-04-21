import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router';
import { AnimatePresence, motion } from 'motion/react';
import { Calendar, LogOut, Menu, User, X } from 'lucide-react';

import { Button } from './ui/shared';
import { useAppContext } from '../context/AppContext';
import { siteContent } from '../config/siteContent';

export const Navbar = ({
  onNavigate,
  isLoggedIn,
  userRole,
}: {
  onNavigate: (path: string) => void;
  isLoggedIn: boolean;
  userRole: 'client' | 'admin' | null;
}) => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const { user, logout } = useAppContext();
  const navigate = useNavigate();

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 10);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navLinks = [
    { label: 'Inicio', path: '/' },
    { label: 'Nosotros', path: '#sobre-nosotros' },
    { label: 'Objetivos', path: '#objetivos' },
    { label: 'Misión y visión', path: '#mision-vision' },
    { label: 'Contacto', path: '#contacto' },
  ];

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <nav
      className={`fixed left-0 right-0 top-0 z-50 transition-all duration-300 ${
        isScrolled
          ? 'bg-[var(--brand-surface)]/95 py-4 shadow-[0_14px_34px_rgba(30,30,28,0.08)] backdrop-blur-md'
          : 'bg-transparent py-6'
      }`}
    >
      <div className="mx-auto flex max-w-7xl items-center justify-between px-6 md:px-12">
        <button
          className="cursor-pointer border-none bg-transparent text-left"
          onClick={() => onNavigate('/')}
        >
          <p className="font-display text-4xl leading-none text-[var(--brand-primary)]">
            {siteContent.brand.name}
          </p>
          <p className="text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">
            {siteContent.brand.subtitle}
          </p>
        </button>

        <div className="hidden items-center gap-8 md:flex">
          {!isLoggedIn &&
            navLinks.map((link) => (
              <button
                key={link.label}
                onClick={() => onNavigate(link.path)}
                className="cursor-pointer font-medium text-[var(--brand-muted)] transition-colors hover:text-[var(--brand-primary)]"
              >
                {link.label}
              </button>
            ))}

          <div className="flex items-center gap-4">
            {isLoggedIn ? (
              <div className="flex items-center gap-4">
                {userRole === 'client' && (
                  <Button
                    variant="ghost"
                    className="flex items-center gap-2"
                    onClick={() => onNavigate('/booking')}
                  >
                    <Calendar size={18} />
                    Mis citas
                  </Button>
                )}
                <div className="flex items-center gap-2 rounded-full border border-[var(--brand-border)] bg-white px-3 py-1.5">
                  <User size={16} className="text-[var(--brand-primary)]" />
                  <span className="text-sm font-medium text-[var(--brand-primary)]">
                    {user?.name}
                  </span>
                </div>
                <button
                  onClick={handleLogout}
                  className="p-2 text-[var(--brand-muted)] transition-colors hover:text-red-500"
                  title="Cerrar sesión"
                >
                  <LogOut size={20} />
                </button>
              </div>
            ) : (
              <Button onClick={() => onNavigate('/login')}>Agenda tu cita</Button>
            )}
          </div>
        </div>

        <button
          className="p-2 text-[var(--brand-primary)] md:hidden"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="overflow-hidden border-b border-[var(--brand-border)] bg-[var(--brand-surface)] md:hidden"
          >
            <div className="flex flex-col gap-6 px-6 py-8">
              {!isLoggedIn &&
                navLinks.map((link) => (
                  <button
                    key={link.label}
                    onClick={() => {
                      onNavigate(link.path);
                      setIsMobileMenuOpen(false);
                    }}
                    className="text-left text-lg font-medium text-[var(--brand-primary)]"
                  >
                    {link.label}
                  </button>
                ))}
              <div className="border-t border-[var(--brand-border)] pt-4">
                {isLoggedIn ? (
                  <div className="space-y-4">
                    <p className="font-semibold text-[var(--brand-primary)]">{user?.name}</p>
                    {userRole === 'client' && (
                      <button
                        onClick={() => {
                          onNavigate('/booking');
                          setIsMobileMenuOpen(false);
                        }}
                        className="block w-full py-2 text-left text-[var(--brand-muted)]"
                      >
                        Mis citas
                      </button>
                    )}
                    <button
                      onClick={handleLogout}
                      className="block w-full py-2 text-left text-red-500"
                    >
                      Cerrar sesión
                    </button>
                  </div>
                ) : (
                  <Button
                    fullWidth
                    onClick={() => {
                      onNavigate('/login');
                      setIsMobileMenuOpen(false);
                    }}
                  >
                    Agenda tu cita
                  </Button>
                )}
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </nav>
  );
};
