import { FormEvent, useState, useMemo, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Eye, EyeOff, Mail, Lock, User, Check, AlertCircle } from 'lucide-react';
import {
    isValidPhoneNumber,
    parsePhoneNumberFromString,
    AsYouType,
    CountryCode,
} from 'libphonenumber-js';

interface Country {
    id: number;
    name: string;
    sortname: string;
    phoneCode: string;
}

interface Props {
    countries: Country[];
    parrain?: string | null;
}

interface RegisterData {
    name: string;
    email: string;
    sexe: string;
    id_country: number | '';
    phone: string;
    password: string;
    password_confirmation: string;
    parrain: string;
    [key: string]: any;
}

// Convertir un code ISO (CM) en emoji drapeau (🇨🇲)
function countryCodeToFlag(isoCode: string): string {
    if (!isoCode) return '🌍';
    return isoCode
        .toUpperCase()
        .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt(0)));
}

export default function Register({ countries, parrain }: Props) {
    const [showPassword, setShowPassword] = useState(false);
    const [phoneTouched, setPhoneTouched] = useState(false);

    const { data, setData, post, processing, errors } = useForm<RegisterData>({
        name: '',
        email: '',
        sexe: '',
        id_country: '',
        phone: '',
        password: '',
        password_confirmation: '',
        parrain: parrain ?? '',
    });

    // Pays sélectionné
    const selectedCountry = useMemo(
        () => countries.find((c) => c.id === Number(data.id_country)) || null,
        [data.id_country, countries]
    );

    // Format phone "as you type" en fonction du pays
    const formatPhone = (value: string, countryCode?: string) => {
        if (!countryCode) return value;
        try {
            const formatter = new AsYouType(countryCode as CountryCode);
            return formatter.input(value);
        } catch {
            return value;
        }
    };

    // Validation locale du téléphone
    const phoneValid = useMemo(() => {
        if (!data.phone || !selectedCountry) return null;
        try {
            return isValidPhoneNumber(data.phone, selectedCountry.sortname as CountryCode);
        } catch {
            return false;
        }
    }, [data.phone, selectedCountry]);

    // Quand on change de pays, on reformate le téléphone
    useEffect(() => {
        if (selectedCountry && data.phone) {
            const reformatted = formatPhone(data.phone, selectedCountry.sortname);
            if (reformatted !== data.phone) {
                setData('phone', reformatted);
            }
        }
    }, [selectedCountry?.id]);

    const handlePhoneChange = (value: string) => {
        const formatted = selectedCountry
            ? formatPhone(value, selectedCountry.sortname)
            : value;
        setData('phone', formatted);
        if (!phoneTouched) setPhoneTouched(true);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();

        // Validation phone côté client avant envoi
        if (selectedCountry && data.phone) {
            const valid = isValidPhoneNumber(
                data.phone,
                selectedCountry.sortname as CountryCode
            );
            if (!valid) {
                alert(`Le numéro de téléphone n'est pas valide pour ${selectedCountry.name}`);
                return;
            }

            // Normaliser au format international (+237...)
            const parsed = parsePhoneNumberFromString(
                data.phone,
                selectedCountry.sortname as CountryCode
            );
            if (parsed) {
                setData('phone', parsed.format('E.164'));
            }
        }

        post('/register');
    };

    return (
        <>
            <Head title="Inscription - Paradisia" />

            <div className="min-h-screen relative overflow-hidden flex items-center justify-center px-4 py-8">
                {/* ===== Image de fond ===== */}
                <div className="absolute inset-0">
                    <img
                        src="https://images.unsplash.com/photo-1546173159-315724a31696?w=1920&q=85&auto=format&fit=crop"
                        alt="Paradisia tropical"
                        className="w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-br from-emerald-950/80 via-emerald-900/60 to-orange-900/40" />
                </div>

                {/* ===== Card ===== */}
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5 }}
                    className="relative z-10 w-full max-w-md my-8"
                >
                    {/* Logo */}
                    <div className="flex justify-center mb-6">
                        <Link href="/" className="block">
                            <div className="w-24 h-24 rounded-full bg-white shadow-2xl flex items-center justify-center p-2 hover:scale-105 transition-transform">
                                <img
                                    src="/images/logo-paradisia.png"
                                    alt="Paradisia"
                                    className="w-full h-full object-contain rounded-full"
                                    onError={(e) => {
                                        e.currentTarget.style.display = 'none';
                                        e.currentTarget.parentElement!.innerHTML =
                                            '<div class="w-full h-full rounded-full bg-gradient-to-br from-emerald-500 to-orange-500 flex items-center justify-center"><span class="text-white font-bold text-3xl">P</span></div>';
                                    }}
                                />
                            </div>
                        </Link>
                    </div>

                    {/* Card formulaire */}
                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <div className="mb-6 text-center">
                            <h1 className="text-2xl font-bold text-zinc-900 mb-1">
                                Créer un compte 🌴
                            </h1>
                            <p className="text-sm text-zinc-500">
                                Rejoignez la communauté Paradisia
                            </p>
                        </div>

                        <form onSubmit={submit} className="space-y-4">
                            {Object.keys(errors).length > 0 && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p className="text-sm font-semibold text-red-700 mb-1">Impossible de créer le compte :</p>
                                    <ul className="text-xs text-red-600 list-disc pl-4 space-y-0.5">
                                        {Object.values(errors).map((msg, i) => <li key={i}>{msg as string}</li>)}
                                    </ul>
                                </div>
                            )}
                            {/* Nom */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Nom complet
                                </label>
                                <div className="relative">
                                    <User className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        autoComplete="name"
                                        autoFocus
                                        required
                                        className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                            errors.name ? 'border-red-300' : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all`}
                                        placeholder="Jean Dupont"
                                    />
                                </div>
                                {errors.name && (
                                    <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                                )}
                            </div>

                            {/* Email */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Email
                                </label>
                                <div className="relative">
                                    <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value.toLowerCase())}
                                        autoComplete="username"
                                        autoCapitalize="none"
                                        autoCorrect="off"
                                        spellCheck={false}
                                        inputMode="email"
                                        required
                                        className={`w-full pl-10 pr-4 py-2.5 bg-zinc-50 border ${
                                            errors.email ? 'border-red-300' : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all`}
                                        placeholder="vous@exemple.com"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-xs text-red-600">{errors.email}</p>
                                )}
                            </div>

                            {/* Sexe (H / F) */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Sexe
                                </label>
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setData('sexe', 'H')}
                                        className={`py-2.5 px-4 rounded-lg border-2 text-sm font-medium transition-all ${
                                            data.sexe === 'H'
                                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                                : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                                        }`}
                                    >
                                        👨 Homme
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setData('sexe', 'F')}
                                        className={`py-2.5 px-4 rounded-lg border-2 text-sm font-medium transition-all ${
                                            data.sexe === 'F'
                                                ? 'border-pink-500 bg-pink-50 text-pink-700'
                                                : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300'
                                        }`}
                                    >
                                        👩 Femme
                                    </button>
                                </div>
                                {errors.sexe && (
                                    <p className="mt-1 text-xs text-red-600">{errors.sexe}</p>
                                )}
                            </div>

                            {/* Pays */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Pays
                                </label>
                                <div className="relative">
                                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-xl pointer-events-none">
                                        {selectedCountry
                                            ? countryCodeToFlag(selectedCountry.sortname)
                                            : '🌍'}
                                    </span>
                                    <select
                                        value={data.id_country}
                                        onChange={(e) =>
                                            setData('id_country', Number(e.target.value))
                                        }
                                        required
                                        className={`w-full pl-12 pr-4 py-2.5 bg-zinc-50 border ${
                                            errors.id_country
                                                ? 'border-red-300'
                                                : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all appearance-none cursor-pointer`}
                                    >
                                        <option value="">Sélectionnez votre pays</option>
                                        {countries.map((country) => (
                                            <option key={country.id} value={country.id}>
                                                {country.name} (+{country.phoneCode})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                {errors.id_country && (
                                    <p className="mt-1 text-xs text-red-600">
                                        {errors.id_country}
                                    </p>
                                )}
                            </div>

                            {/* Téléphone avec drapeau et indicatif */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Téléphone
                                </label>
                                <div className="relative">
                                    {/* Drapeau + indicatif */}
                                    <div className="absolute left-0 top-0 bottom-0 flex items-center gap-1 pl-3 pr-2 border-r border-zinc-200 bg-zinc-100 rounded-l-lg pointer-events-none">
                                        <span className="text-xl">
                                            {selectedCountry
                                                ? countryCodeToFlag(selectedCountry.sortname)
                                                : '🌍'}
                                        </span>
                                        <span className="text-sm font-medium text-zinc-700">
                                            +{selectedCountry?.phoneCode || '—'}
                                        </span>
                                    </div>

                                    <input
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => handlePhoneChange(e.target.value)}
                                        onBlur={() => setPhoneTouched(true)}
                                        disabled={!selectedCountry}
                                        autoComplete="tel"
                                        required
                                        className={`w-full pl-[100px] pr-10 py-2.5 bg-zinc-50 border ${
                                            errors.phone || (phoneTouched && phoneValid === false)
                                                ? 'border-red-300'
                                                : phoneTouched && phoneValid === true
                                                ? 'border-emerald-300'
                                                : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all disabled:bg-zinc-100 disabled:cursor-not-allowed`}
                                        placeholder={
                                            selectedCountry
                                                ? '6 12 34 56 78'
                                                : "Sélectionnez d'abord un pays"
                                        }
                                    />

                                    {/* Indicateur visuel de validité */}
                                    {phoneTouched && data.phone && (
                                        <div className="absolute right-3 top-1/2 -translate-y-1/2">
                                            {phoneValid ? (
                                                <Check className="w-5 h-5 text-emerald-500" />
                                            ) : (
                                                <AlertCircle className="w-5 h-5 text-red-500" />
                                            )}
                                        </div>
                                    )}
                                </div>

                                {/* Messages */}
                                {!selectedCountry && (
                                    <p className="mt-1 text-xs text-zinc-500">
                                        Sélectionnez d'abord votre pays pour activer la
                                        validation
                                    </p>
                                )}
                                {phoneTouched && phoneValid === false && data.phone && (
                                    <p className="mt-1 text-xs text-red-600 flex items-center gap-1">
                                        <AlertCircle className="w-3 h-3" />
                                        Numéro invalide pour {selectedCountry?.name}
                                    </p>
                                )}
                                {phoneTouched && phoneValid === true && (
                                    <p className="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                                        <Check className="w-3 h-3" />
                                        Numéro valide
                                    </p>
                                )}
                                {errors.phone && (
                                    <p className="mt-1 text-xs text-red-600">{errors.phone}</p>
                                )}
                            </div>

                            {/* Password */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Mot de passe
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        autoComplete="new-password"
                                        required
                                        className={`w-full pl-10 pr-10 py-2.5 bg-zinc-50 border ${
                                            errors.password ? 'border-red-300' : 'border-zinc-200'
                                        } rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all`}
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="w-5 h-5" />
                                        ) : (
                                            <Eye className="w-5 h-5" />
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-xs text-red-600">{errors.password}</p>
                                )}
                            </div>

                            {/* Confirm Password */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">
                                    Confirmer le mot de passe
                                </label>
                                <div className="relative">
                                    <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400" />
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password_confirmation}
                                        onChange={(e) =>
                                            setData('password_confirmation', e.target.value)
                                        }
                                        autoComplete="new-password"
                                        required
                                        className="w-full pl-10 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                        placeholder="••••••••"
                                    />
                                </div>
                            </div>

                            {/* Code de parrainage (optionnel, pré-rempli par le lien) */}
                            <div>
                                <label className="block text-sm font-medium text-zinc-700 mb-1.5">Code de parrainage (optionnel)</label>
                                <input
                                    type="text"
                                    value={data.parrain}
                                    onChange={(e) => setData('parrain', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all font-mono"
                                    placeholder="REF_XXXXXXXXXX"
                                />
                                {parrain && <p className="mt-1 text-xs text-emerald-600">Tu t'inscris grâce à un parrain 🎉</p>}
                                {errors.parrain && <p className="mt-1 text-xs text-red-600">{errors.parrain}</p>}
                            </div>

                            {/* Bouton submit */}
                            <button
                                type="submit"
                                disabled={processing || (!!data.phone && phoneValid === false)}
                                className="w-full py-3 bg-emerald-500 hover:bg-emerald-600 disabled:bg-emerald-300 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors shadow-sm hover:shadow-md mt-2"
                            >
                                {processing ? 'Création en cours...' : 'Créer mon compte'}
                            </button>
                        </form>

                        {/* Lien vers login */}
                        <p className="mt-6 text-center text-sm text-zinc-600">
                            Vous avez déjà un compte ?{' '}
                            <Link
                                href="/login"
                                className="text-emerald-600 hover:text-emerald-700 font-semibold"
                            >
                                Se connecter
                            </Link>
                        </p>
                    </div>

                    {/* Footer */}
                    <p className="text-center text-xs text-white/70 mt-6">
                        © {new Date().getFullYear()} Paradisia Africa. Tous droits réservés.
                    </p>
                </motion.div>
            </div>
        </>
    );
}