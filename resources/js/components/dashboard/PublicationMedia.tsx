import { useState, useEffect } from 'react';
import { X, ChevronLeft, ChevronRight, Music } from 'lucide-react';
import type { Publication } from '@/types';

interface Props {
    publication: Publication;
}

export default function PublicationMedia({ publication }: Props) {
    const [showLightbox, setShowLightbox] = useState(false);
    const [currentIndex, setCurrentIndex] = useState(0);

    const images = publication.images;
    const imageCount = images.length;

    useEffect(() => {
        if (!showLightbox) return;

        const handleKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setShowLightbox(false);
            if (e.key === 'ArrowLeft')
                setCurrentIndex((prev) => (prev - 1 + imageCount) % imageCount);
            if (e.key === 'ArrowRight') setCurrentIndex((prev) => (prev + 1) % imageCount);
        };

        window.addEventListener('keydown', handleKey);
        return () => window.removeEventListener('keydown', handleKey);
    }, [showLightbox, imageCount]);

    const openLightbox = (index: number) => {
        setCurrentIndex(index);
        setShowLightbox(true);
    };

    return (
        <>
            {/* Images */}
            {imageCount > 0 && (
                <div className="relative">
                    {imageCount === 1 && (
                        <div
                            className="cursor-pointer"
                            onClick={() => openLightbox(0)}
                        >
                            <img loading="lazy" decoding="async"
                                src={images[0]}
                                alt="Publication"
                                className="w-full object-cover max-h-[500px] hover:opacity-95 transition-opacity"
                            />
                        </div>
                    )}

                    {imageCount === 2 && (
                        <div className="grid grid-cols-2 gap-1">
                            {images.map((img, i) => (
                                <div
                                    key={i}
                                    className="cursor-pointer aspect-square overflow-hidden"
                                    onClick={() => openLightbox(i)}
                                >
                                    <img loading="lazy" decoding="async"
                                        src={img}
                                        alt="Publication"
                                        className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    {imageCount === 3 && (
                        <div className="grid grid-cols-2 gap-1">
                            <div
                                className="row-span-2 cursor-pointer"
                                onClick={() => openLightbox(0)}
                            >
                                <img loading="lazy" decoding="async"
                                    src={images[0]}
                                    alt="Publication"
                                    className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                />
                            </div>
                            <div
                                className="cursor-pointer aspect-square overflow-hidden"
                                onClick={() => openLightbox(1)}
                            >
                                <img loading="lazy" decoding="async"
                                    src={images[1]}
                                    alt="Publication"
                                    className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                />
                            </div>
                            <div
                                className="cursor-pointer aspect-square overflow-hidden"
                                onClick={() => openLightbox(2)}
                            >
                                <img loading="lazy" decoding="async"
                                    src={images[2]}
                                    alt="Publication"
                                    className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                />
                            </div>
                        </div>
                    )}

                    {imageCount === 4 && (
                        <div className="grid grid-cols-2 gap-1">
                            {images.map((img, i) => (
                                <div
                                    key={i}
                                    className="cursor-pointer aspect-square overflow-hidden"
                                    onClick={() => openLightbox(i)}
                                >
                                    <img loading="lazy" decoding="async"
                                        src={img}
                                        alt="Publication"
                                        className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    {imageCount >= 5 && (
                        <div className="grid grid-cols-2 gap-1">
                            <div
                                className="col-span-2 cursor-pointer"
                                onClick={() => openLightbox(0)}
                            >
                                <img loading="lazy" decoding="async"
                                    src={images[0]}
                                    alt="Publication"
                                    className="w-full h-64 object-cover hover:opacity-95 transition-opacity"
                                />
                            </div>
                            {[1, 2, 3].map((i) => (
                                <div
                                    key={i}
                                    className="cursor-pointer aspect-square overflow-hidden relative"
                                    onClick={() => openLightbox(i)}
                                >
                                    <img loading="lazy" decoding="async"
                                        src={images[i]}
                                        alt="Publication"
                                        className="w-full h-full object-cover hover:opacity-95 transition-opacity"
                                    />
                                    {i === 3 && imageCount > 4 && (
                                        <div className="absolute inset-0 bg-black/60 flex items-center justify-center">
                                            <span className="text-white text-3xl font-bold">
                                                +{imageCount - 4}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* Vidéo */}
            {publication.video && (
                <div className="relative bg-black">
                    <video
                        className="w-full max-h-[500px]"
                        controls
                        preload="metadata"
                        poster={imageCount > 0 ? images[0] : undefined}
                    >
                        <source src={publication.video} type="video/mp4" />
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                </div>
            )}

            {/* Audio */}
            {publication.audio && (
                <div className="px-4 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 border-t border-b border-gray-100">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <Music className="w-6 h-6 text-white" />
                        </div>
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-700 mb-1">🎵 Audio</p>
                            <audio controls className="w-full h-10">
                                <source src={publication.audio} type="audio/mpeg" />
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                        </div>
                    </div>
                </div>
            )}

            {/* Lightbox */}
            {showLightbox && (
                <div className="fixed inset-0 z-50 bg-black/95 flex items-center justify-center animate-fade-in">
                    <button
                        onClick={() => setShowLightbox(false)}
                        className="absolute top-4 right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all"
                    >
                        <X className="w-8 h-8 text-white" />
                    </button>

                    {imageCount > 1 && (
                        <>
                            <button
                                onClick={() =>
                                    setCurrentIndex(
                                        (prev) => (prev - 1 + imageCount) % imageCount
                                    )
                                }
                                className="absolute left-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all"
                            >
                                <ChevronLeft className="w-8 h-8 text-white" />
                            </button>
                            <button
                                onClick={() =>
                                    setCurrentIndex((prev) => (prev + 1) % imageCount)
                                }
                                className="absolute right-4 z-10 p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all"
                            >
                                <ChevronRight className="w-8 h-8 text-white" />
                            </button>
                        </>
                    )}

                    <div className="max-w-5xl max-h-[90vh] px-16">
                        <img loading="lazy" decoding="async"
                            src={images[currentIndex]}
                            alt="Publication"
                            className="max-w-full max-h-[85vh] object-contain mx-auto rounded-lg shadow-2xl"
                        />
                    </div>

                    {imageCount > 1 && (
                        <>
                            <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-2">
                                {images.map((_, i) => (
                                    <button
                                        key={i}
                                        onClick={() => setCurrentIndex(i)}
                                        className={`h-2.5 rounded-full transition-all ${
                                            currentIndex === i
                                                ? 'bg-white w-8'
                                                : 'bg-white/50 hover:bg-white/80 w-2.5'
                                        }`}
                                    />
                                ))}
                            </div>

                            <div className="absolute top-4 left-4 bg-black/50 text-white px-4 py-2 rounded-full text-sm font-medium">
                                {currentIndex + 1} / {imageCount}
                            </div>
                        </>
                    )}
                </div>
            )}

            <style>{`
                @keyframes fade-in {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                .animate-fade-in { animation: fade-in 0.3s ease-out; }
            `}</style>
        </>
    );
}