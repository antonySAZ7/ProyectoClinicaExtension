import { type ReactNode } from 'react';
import { useNavigate } from 'react-router';
import { ArrowRight, HeartHandshake, Instagram, Mail, Phone, Sparkles } from 'lucide-react';

import { Card, Button } from './ui/shared';
import { ImageWithFallback } from './figma/ImageWithFallback';
import { siteContent } from '../config/siteContent';

export const Home = () => {
  const navigate = useNavigate();

  const scrollToSection = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <div className="pt-20 text-[var(--brand-primary)]">
      <section className="relative flex min-h-[720px] items-center overflow-hidden px-6 py-20 md:px-12">
        <div className="absolute inset-0 bg-[linear-gradient(180deg,var(--brand-surface),var(--brand-soft))]" />

        <div className="relative z-10 mx-auto grid w-full max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
          <div className="max-w-3xl">
            <p className="mb-3 text-4xl md:text-6xl font-script text-[var(--brand-muted)]">
              {siteContent.brand.subtitle}
            </p>
            <h1 className="font-display text-6xl md:text-8xl leading-none tracking-[0.08em] text-[var(--brand-primary)]">
              {siteContent.brand.name}
            </h1>
            <p className="mt-6 max-w-2xl text-lg md:text-2xl text-[var(--brand-primary)]/90">
              {siteContent.brand.tagline}
            </p>
            <p className="mt-6 max-w-2xl text-base md:text-lg text-[var(--brand-muted)]">
              Atención odontológica accesible, preventiva y centrada en las personas.
              Un proyecto humano que busca transformar sonrisas en Guatemala.
            </p>
          </div>

          <div className="flex flex-col gap-4 sm:flex-row">
            <Button className="px-8 py-4 text-base" onClick={() => navigate('/login')}>
              Agenda tu cita
            </Button>
            <Button
              variant="outline"
              className="px-8 py-4 text-base"
              onClick={() => scrollToSection('contacto')}
            >
              Ver contacto
            </Button>
          </div>

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <HeroInfo
              icon={<Phone size={18} />}
              label="Teléfono"
              value={siteContent.brand.phoneDisplay}
            />
            <HeroInfo
              icon={<Instagram size={18} />}
              label="Instagram"
              value={siteContent.brand.instagramDisplay}
            />
            <HeroInfo
              icon={<Mail size={18} />}
              label="Correo"
              value={siteContent.brand.emailDisplay}
              className="sm:col-span-2"
              valueClassName="text-lg md:text-xl"
            />
          </div>

          <div className="relative">
            <div className="absolute -left-6 top-10 hidden h-48 w-48 rounded-full bg-white/60 blur-3xl lg:block" />
            <div className="grid grid-cols-2 gap-4 md:gap-6">
              <HeroPhotoCard
                src={siteContent.photos.heroPrimary}
                alt={`${siteContent.brand.legalName} - vista principal 1`}
                objectPosition={siteContent.framing.heroPrimary}
                className="mt-0 md:mt-10"
              />
              <HeroPhotoCard
                src={siteContent.photos.heroSecondary}
                alt={`${siteContent.brand.legalName} - vista principal 2`}
                objectPosition={siteContent.framing.heroSecondary}
                className="mt-12 md:mt-0"
              />
            </div>
          </div>
        </div>
      </section>

      <section className="bg-[var(--brand-surface)] px-6 py-24 md:px-12" id="sobre-nosotros">
        <div className="mx-auto grid max-w-7xl gap-16 lg:grid-cols-[1.1fr_0.9fr]">
          <div>
            <SectionHeading
              eyebrow={siteContent.about.eyebrow}
              title={siteContent.about.title}
            />
            <div className="space-y-6 text-lg leading-relaxed text-[var(--brand-muted)]">
              {siteContent.about.paragraphs.map((paragraph) => (
                <p key={paragraph}>{paragraph}</p>
              ))}
            </div>

            <div className="mt-10 grid gap-4 sm:grid-cols-2">
              <MiniInfo
                icon={<HeartHandshake size={20} />}
                title="Atención con empatía"
                description="Buscamos acompañar a cada paciente con calidez humana y cercanía."
              />
              <MiniInfo
                icon={<Sparkles size={20} />}
                title="Propósito comunitario"
                description="Queremos llevar salud bucal de calidad a quienes más la necesitan."
              />
            </div>
          </div>

          <div className="space-y-6">
            <Card className="overflow-hidden p-0">
              <div className="relative aspect-[4/5] sm:aspect-[5/4] lg:aspect-[4/5]">
                <ImageWithFallback
                  src={siteContent.photos.aboutTeam}
                  alt={`${siteContent.brand.legalName} - equipo fundador`}
                  className="h-full w-full object-cover"
                  style={{ objectPosition: siteContent.framing.aboutTeam }}
                />
                <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,35,31,0.04),rgba(36,35,31,0.5))]" />
                <div className="absolute bottom-0 left-0 p-6 text-white">
                  <p className="text-xs uppercase tracking-[0.3em] text-white/75">Equipo fundador</p>
                  <p className="font-display text-4xl leading-none">
                    {siteContent.brand.founders.join(' y ')}
                  </p>
                </div>
              </div>
            </Card>

            <Card className="bg-white">
              <p className="text-xs uppercase tracking-[0.28em] text-[var(--brand-muted)]">DENS32</p>
              <p className="mt-3 font-display text-3xl text-[var(--brand-primary)]">
                Un proyecto creado por {siteContent.brand.founders[0]} y {siteContent.brand.founders[1]}.
              </p>
              <p className="mt-3 text-sm leading-relaxed text-[var(--brand-muted)]">
                Combinamos formación académica, sensibilidad social y vocación de servicio
                para ofrecer una atención dental cercana, ética y comprometida con la comunidad.
              </p>
            </Card>
          </div>
        </div>
      </section>

      <section className="bg-[var(--brand-soft)] px-6 py-24 md:px-12" id="objetivos">
        <div className="mx-auto max-w-7xl">
          <SectionHeading
            eyebrow="Nuestros objetivos"
            title="Lo que queremos construir con DENS32"
            centered
          />
          <div className="mt-14 grid gap-6 md:grid-cols-2">
            {siteContent.objectives.map((objective, index) => (
              <Card key={objective.title} className="h-full bg-white">
                <div className="mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[var(--brand-border)] bg-[var(--brand-soft)] font-display text-4xl text-[var(--brand-primary)]">
                  {index + 1}
                </div>
                <h3 className="font-display text-3xl leading-tight text-[var(--brand-primary)]">
                  {objective.title}
                </h3>
                <p className="mt-4 text-base leading-relaxed text-[var(--brand-muted)]">
                  {objective.description}
                </p>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-[var(--brand-surface)] px-6 py-24 md:px-12" id="mision-vision">
        <div className="mx-auto flex max-w-7xl flex-col gap-10">
          <InfoPanel
            eyebrow="Nuestra misión"
            title={siteContent.mission.title}
            description={siteContent.mission.description}
            image={siteContent.photos.mission}
            objectPosition={siteContent.framing.mission}
            imageAlt="Misión de DENS32"
          />
          <InfoPanel
            eyebrow="Nuestra visión"
            title={siteContent.vision.title}
            description={siteContent.vision.description}
            image={siteContent.photos.vision}
            objectPosition={siteContent.framing.vision}
            imageAlt="Visión de DENS32"
            reverse
          />
        </div>
      </section>

      <section className="bg-[var(--brand-soft)] px-6 py-24 md:px-12" id="contacto">
        <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.95fr_1.05fr]">
          <div>
            <SectionHeading
              eyebrow="Contacto"
              title="Vivimos para servirte"
            />
            <p className="max-w-xl text-lg leading-relaxed text-[var(--brand-muted)]">
              Si quieres empezar con tus fotos reales, la app ya quedó lista para eso.
              Solo necesitas reemplazar las imágenes configuradas en un solo archivo y el
              sitio tomará los cambios automáticamente.
            </p>

            <div className="mt-10 grid gap-4">
              <ContactCard
                icon={<Phone size={20} />}
                label="Teléfono"
                value={siteContent.brand.phoneDisplay}
                href={siteContent.brand.phoneHref}
              />
              <ContactCard
                icon={<Instagram size={20} />}
                label="Instagram"
                value={siteContent.brand.instagramDisplay}
                href={siteContent.brand.instagramHref}
              />
              <ContactCard
                icon={<Mail size={20} />}
                label="Correo"
                value={siteContent.brand.emailDisplay}
                href={siteContent.brand.emailHref}
              />
            </div>
          </div>

          <Card className="overflow-hidden p-0">
            <div className="relative h-full min-h-[420px]">
              <ImageWithFallback
                src={siteContent.photos.contact}
                alt={`Contacto ${siteContent.brand.legalName}`}
                className="h-full w-full object-cover"
                style={{ objectPosition: siteContent.framing.contact }}
              />
              <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,35,31,0.18),rgba(36,35,31,0.62))]" />
              <div className="absolute inset-x-0 bottom-0 p-8 text-white">
                <p className="font-script text-4xl">Clínica Dental</p>
                <p className="font-display text-6xl leading-none tracking-[0.08em]">
                  {siteContent.brand.name}
                </p>
                <p className="mt-4 max-w-md text-white/85">
                  Estamos listos para recibirte con un espacio cómodo, moderno y pensado para
                  brindarte una experiencia segura desde tu primera visita.
                </p>
              </div>
            </div>
          </Card>
        </div>
      </section>
    </div>
  );
};

const SectionHeading = ({
  eyebrow,
  title,
  centered = false,
}: {
  eyebrow: string;
  title: string;
  centered?: boolean;
}) => (
  <div className={centered ? 'mb-12 text-center' : 'mb-12'}>
    <p className="text-4xl text-[var(--brand-muted)] md:text-5xl font-script">{eyebrow}</p>
    <h2 className="font-display text-5xl leading-none text-[var(--brand-primary)] md:text-7xl">
      {title}
    </h2>
    <div
      className={`mt-5 h-px bg-[var(--brand-primary)]/70 ${
        centered ? 'mx-auto w-48' : 'w-40 md:w-52'
      }`}
    />
  </div>
);

const HeroInfo = ({
  icon,
  label,
  value,
  className = '',
  valueClassName = '',
}: {
  icon: ReactNode;
  label: string;
  value: string;
  className?: string;
  valueClassName?: string;
}) => (
  <div className={`min-w-0 rounded-[24px] border border-white/70 bg-white/88 px-5 py-5 shadow-[0_18px_40px_rgba(36,35,31,0.08)] backdrop-blur-md ${className}`}>
    <div className="flex min-w-0 items-start gap-4">
      <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--brand-soft)] text-[var(--brand-primary)] shadow-inner">
        {icon}
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-[11px] font-semibold uppercase tracking-[0.28em] text-[var(--brand-muted)]">
          {label}
        </p>
        <p
          className={`mt-2 text-xl font-medium leading-snug text-[var(--brand-primary)] [overflow-wrap:anywhere] ${valueClassName}`}
        >
          {value}
        </p>
      </div>
    </div>
  </div>
);

const HeroPhotoCard = ({
  src,
  alt,
  objectPosition,
  className = '',
}: {
  src: string;
  alt: string;
  objectPosition?: string;
  className?: string;
}) => (
  <Card className={`overflow-hidden p-0 ${className}`}>
    <div className="relative aspect-[4/5] md:aspect-[3/4]">
      <ImageWithFallback
        src={src}
        alt={alt}
        className="h-full w-full object-cover"
        style={objectPosition ? { objectPosition } : undefined}
      />
      <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0),rgba(36,35,31,0.22))]" />
    </div>
  </Card>
);

const MiniInfo = ({
  icon,
  title,
  description,
}: {
  icon: ReactNode;
  title: string;
  description: string;
}) => (
  <Card className="bg-white">
    <div className="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--brand-soft)] text-[var(--brand-primary)]">
      {icon}
    </div>
    <h3 className="mt-4 font-display text-3xl text-[var(--brand-primary)]">{title}</h3>
    <p className="mt-3 text-sm leading-relaxed text-[var(--brand-muted)]">{description}</p>
  </Card>
);

const InfoPanel = ({
  eyebrow,
  title,
  description,
  image,
  imageAlt,
  objectPosition,
  reverse = false,
}: {
  eyebrow: string;
  title: string;
  description: string;
  image: string;
  imageAlt: string;
  objectPosition?: string;
  reverse?: boolean;
}) => (
  <Card className="overflow-hidden p-0">
    <div className={`grid gap-0 lg:grid-cols-2 ${reverse ? 'lg:[&>*:first-child]:order-2' : ''}`}>
      <div className="flex items-center px-8 py-10 md:px-12">
        <div>
          <p className="text-4xl text-[var(--brand-muted)] font-script">{eyebrow}</p>
          <h3 className="font-display text-5xl leading-none text-[var(--brand-primary)] md:text-6xl">
            {title}
          </h3>
          <div className="mt-5 h-px w-32 bg-[var(--brand-primary)]/70" />
          <p className="mt-8 text-lg leading-relaxed text-[var(--brand-muted)]">
            {description}
          </p>
        </div>
      </div>
      <div className="min-h-[360px]">
        <ImageWithFallback
          src={image}
          alt={imageAlt}
          className="h-full w-full object-cover"
          style={objectPosition ? { objectPosition } : undefined}
        />
      </div>
    </div>
  </Card>
);

const ContactCard = ({
  icon,
  label,
  value,
  href,
}: {
  icon: ReactNode;
  label: string;
  value: string;
  href: string;
}) => (
  <a
    href={href}
    target={href.startsWith('http') ? '_blank' : undefined}
    rel={href.startsWith('http') ? 'noreferrer' : undefined}
    className="flex min-w-0 items-center gap-4 rounded-2xl border border-[var(--brand-border)] bg-white px-5 py-4 transition-colors hover:border-[var(--brand-primary)]"
  >
    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--brand-soft)] text-[var(--brand-primary)]">
      {icon}
    </div>
    <div className="min-w-0 flex-1">
      <p className="text-sm uppercase tracking-[0.2em] text-[var(--brand-muted)]">{label}</p>
      <p className="text-base leading-snug text-[var(--brand-primary)] [overflow-wrap:anywhere]">{value}</p>
    </div>
    <ArrowRight className="ml-auto shrink-0 text-[var(--brand-muted)]" size={18} />
  </a>
);
