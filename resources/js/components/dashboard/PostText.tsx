import { useState } from 'react';
import { Play, ExternalLink } from 'lucide-react';

const URL_RE = /(https?:\/\/[^\s]+)/g;

/** Extrait l'identifiant d'une vidéo YouTube d'une URL (watch, youtu.be, shorts, embed). */
export function extraireYoutube(texte: string): { id: string; url: string } | null {
    const m = texte.match(/https?:\/\/(?:www\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    return m ? { id: m[1], url: m[0] } : null;
}

/** Texte avec les liens rendus cliquables. */
export function TexteLie({ texte }: { texte: string }) {
    const parts = texte.split(URL_RE);
    return (
        <p className="text-zinc-800 text-[15px] leading-relaxed whitespace-pre-line break-words">
            {parts.map((part, i) =>
                /^https?:\/\//.test(part) ? (
                    <a key={i} href={part} target="_blank" rel="noopener noreferrer"
                        className="text-emerald-600 font-medium hover:underline break-all">
                        {part}
                    </a>
                ) : (
                    <span key={i}>{part}</span>
                ),
            )}
        </p>
    );
}

/** Prévisualisation d'une vidéo YouTube (miniature cliquable → lecteur intégré). */
export function ApercuYoutube({ id, url }: { id: string; url: string }) {
    const [lecture, setLecture] = useState(false);

    if (lecture) {
        return (
            <div className="mt-3 rounded-xl overflow-hidden bg-black aspect-video">
                <iframe
                    src={`https://www.youtube.com/embed/${id}?autoplay=1&rel=0`}
                    title="Vidéo YouTube"
                    className="w-full h-full"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                />
            </div>
        );
    }

    return (
        <div className="mt-3 rounded-xl overflow-hidden border border-zinc-200">
            <button onClick={() => setLecture(true)} className="relative block w-full group">
                <img
                    src={`https://img.youtube.com/vi/${id}/hqdefault.jpg`}
                    alt="Aperçu de la vidéo"
                    className="w-full aspect-video object-cover"
                    onError={(e) => { e.currentTarget.src = `https://img.youtube.com/vi/${id}/0.jpg`; }}
                />
                <span className="absolute inset-0 bg-black/15 group-hover:bg-black/25 flex items-center justify-center transition-colors">
                    <span className="w-16 h-11 rounded-xl bg-red-600 group-hover:bg-red-700 flex items-center justify-center shadow-lg transition-colors">
                        <Play className="w-6 h-6 text-white ml-0.5" fill="currentColor" />
                    </span>
                </span>
            </button>
            <a href={url} target="_blank" rel="noopener noreferrer"
                className="flex items-center gap-2 px-3 py-2.5 bg-zinc-50 hover:bg-zinc-100 transition-colors">
                <span className="w-6 h-6 rounded-md bg-red-600 flex items-center justify-center flex-shrink-0">
                    <Play className="w-3.5 h-3.5 text-white ml-0.5" fill="currentColor" />
                </span>
                <span className="flex-1 min-w-0 text-sm font-medium text-zinc-700 truncate">Regarder sur YouTube</span>
                <ExternalLink className="w-4 h-4 text-zinc-400 flex-shrink-0" />
            </a>
        </div>
    );
}
