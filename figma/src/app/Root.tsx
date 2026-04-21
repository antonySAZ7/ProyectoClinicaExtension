import React from 'react';
import { Outlet, useLocation, useNavigate } from 'react-router';
import { Instagram, Mail, Phone } from 'lucide-react';
import { Toaster } from 'sonner';

import { Navbar } from './components/Navbar';
import { useAppContext } from './context/AppContext';
import { siteContent } from './config/siteContent';

export const Root = () => {
  const { user } = useAppContext();
  const location = useLocation();
  const navigate = useNavigate();

  const isDashboard = location.pathname.startsWith('/admin');
  const isAuth =
    location.pathname === '/login' ||
    location.pathname === '/register' ||
    location.pathname === '/admin/login';

  const handleNavigate = (path: string) => {
    if (path.startsWith('#')) {
      const id = path.substring(1);
      if (location.pathname !== '/') {
        navigate('/');
        setTimeout(() => {
          document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
        }, 100);
      } else {
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
      }
    } else {
      navigate(path);
    }
  };

  return (
    <div className="min-h-screen bg-[var(--brand-soft)] font-inter text-[var(--brand-primary)]">
      <Toaster position="top-right" expand richColors />

      {!isDashboard && !isAuth && (
        <Navbar
          onNavigate={handleNavigate}
          isLoggedIn={!!user}
          userRole={user?.role || null}
        />
      )}

      <main>
        <Outlet />
      </main>

      {!isDashboard && !isAuth && (
        <footer className="border-t border-[var(--brand-border)] bg-[var(--brand-surface)] py-12">
          <div className="mx-auto flex max-w-7xl flex-col gap-8 px-6 md:flex-row md:items-center md:justify-between md:px-12">
            <div>
              <p className="font-display text-4xl leading-none text-[var(--brand-primary)]">
                {siteContent.brand.name}
              </p>
              <p className="mt-2 text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">
                {siteContent.brand.subtitle}
              </p>
              <p className="mt-4 text-sm text-[var(--brand-muted)]">
                © {siteContent.brand.year} {siteContent.brand.legalName}. Todos los derechos
                reservados.
              </p>
            </div>

            <div className="flex flex-col gap-3 text-sm text-[var(--brand-muted)]">
              <a
                href={siteContent.brand.phoneHref}
                className="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
              >
                <Phone size={16} />
                {siteContent.brand.phoneDisplay}
              </a>
              <a
                href={siteContent.brand.instagramHref}
                target="_blank"
                rel="noreferrer"
                className="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
              >
                <Instagram size={16} />
                {siteContent.brand.instagramDisplay}
              </a>
              <a
                href={siteContent.brand.emailHref}
                className="flex items-center gap-2 transition-colors hover:text-[var(--brand-primary)]"
              >
                <Mail size={16} />
                {siteContent.brand.emailDisplay}
              </a>
            </div>
          </div>
        </footer>
      )}
    </div>
  );
};
