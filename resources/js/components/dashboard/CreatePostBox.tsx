import type { User } from '@/types';

interface Props {
    user: User | null;
    onOpen: () => void;
}

export default function CreatePostBox({ user, onOpen }: Props) {
    const avatarUrl = user
        ? user.photo ||
          `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=8b5cf6&color=fff&size=64`
        : 'https://ui-avatars.com/api/?name=You&background=8b5cf6&color=fff&size=64';

    return (
        <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100 hover:shadow-2xl transition-all duration-300">
            <div className="flex items-center gap-4 mb-4">
                <img
                    src={avatarUrl}
                    alt="Your avatar"
                    className="w-12 h-12 rounded-full border-2 border-purple-400 object-cover"
                />
                <input
                    type="text"
                    onClick={onOpen}
                    readOnly
                    placeholder="Partagez votre expérience Paradisia... 🌴"
                    className="flex-1 bg-gray-100 rounded-full px-6 py-3 cursor-pointer hover:bg-gray-200 transition-all outline-none"
                />
            </div>
            <div className="flex items-center justify-between pt-4 border-t border-gray-100">
                <div className="flex gap-2 flex-wrap">
                    <ActionButton onClick={onOpen} icon="📸" label="Photo" color="green" />
                    <ActionButton onClick={onOpen} icon="🎥" label="Vidéo" color="blue" />
                    <ActionButton onClick={onOpen} icon="😊" label="Humeur" color="yellow" />
                </div>
            </div>
        </div>
    );
}

function ActionButton({
    onClick,
    icon,
    label,
    color,
}: {
    onClick: () => void;
    icon: string;
    label: string;
    color: 'green' | 'blue' | 'yellow';
}) {
    const colors = {
        green: 'hover:bg-emerald-50 text-emerald-600',
        blue: 'hover:bg-blue-50 text-blue-600',
        yellow: 'hover:bg-amber-50 text-amber-600',
    };

    return (
        <button
            onClick={onClick}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl transition-all ${colors[color]}`}
        >
            <span className="text-xl">{icon}</span>
            <span className="text-sm font-semibold">{label}</span>
        </button>
    );
}