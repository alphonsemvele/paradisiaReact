import { Link } from '@inertiajs/react';

interface Service {
    icon: string;
    title: string;
    description: string;
    href: string;
    gradient: string;
    border: string;
    bg: string;
}

const services: Service[] = [
    {
        icon: '💒',
        title: 'Prestations Événements',
        description: 'Mariages, anniversaires, baptêmes',
        href: '#',
        gradient: 'from-pink-50 to-purple-50',
        border: 'border-pink-200',
        bg: 'bg-pink-500',
    },
    {
        icon: '🎓',
        title: 'Formation Présentiel',
        description: 'Production de jus naturels',
        href: '#',
        gradient: 'from-blue-50 to-cyan-50',
        border: 'border-blue-200',
        bg: 'bg-blue-500',
    },
    {
        icon: '💻',
        title: 'Formation En Ligne',
        description: 'Cours vidéo, webinaires',
        href: '#',
        gradient: 'from-emerald-50 to-teal-50',
        border: 'border-emerald-200',
        bg: 'bg-emerald-500',
    },
    {
        icon: '🏪',
        title: 'Franchise PARADISIA',
        description: 'Ouvrez votre point de vente',
        href: '#',
        gradient: 'from-purple-50 to-indigo-50',
        border: 'border-purple-200',
        bg: 'bg-purple-500',
    },
];

export default function ServicesSection() {
    return (
        <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100">
            <h4 className="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span className="text-2xl">🎯</span>
                Nos Services
            </h4>
            <div className="space-y-3">
                {services.map((service, index) => (
                    <ServiceCard key={index} service={service} />
                ))}
            </div>
        </div>
    );
}

function ServiceCard({ service }: { service: Service }) {
    return (
        <Link
            href={service.href}
            className={`block p-4 rounded-xl bg-gradient-to-br ${service.gradient} border-2 ${service.border} hover:shadow-lg transition-all group`}
        >
            <div className="flex items-start gap-3">
                <div
                    className={`${service.bg} text-white p-3 rounded-lg text-xl group-hover:scale-110 transition-transform`}
                >
                    {service.icon}
                </div>
                <div className="flex-1">
                    <h5 className="font-bold text-gray-800 mb-1 text-sm">{service.title}</h5>
                    <p className="text-xs text-gray-600">{service.description}</p>
                </div>
            </div>
        </Link>
    );
}