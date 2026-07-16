/**
 * Compresse une image côté navigateur AVANT l'upload : redimensionne à
 * 1600 px max et ré-encode (JPEG/PNG). Une photo de téléphone de 8 Mo part
 * en ~200-400 Ko : l'envoi devient quasi instantané même sur une connexion
 * lente. Le serveur ré-optimise ensuite de toute façon (ImageOptimizer).
 *
 * Sans risque : si le fichier n'est pas une image gérée ou que le
 * traitement échoue, le fichier original est envoyé tel quel.
 */
export async function resizeImageFile(
    file: File,
    maxDim = 1600,
    quality = 0.8
): Promise<File> {
    if (!file.type.startsWith('image/') || file.type === 'image/gif') {
        return file;
    }

    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, maxDim / Math.max(bitmap.width, bitmap.height));

        // Déjà petite et légère : inutile de ré-encoder
        if (scale === 1 && file.size < 400 * 1024) {
            bitmap.close();
            return file;
        }

        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            bitmap.close();
            return file;
        }

        ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        // PNG conservé (transparence possible), tout le reste part en JPEG
        const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        const blob = await new Promise<Blob | null>((resolve) =>
            canvas.toBlob(resolve, type, quality)
        );

        if (!blob || blob.size >= file.size) {
            return file;
        }

        const name = file.name.replace(/\.\w+$/, type === 'image/png' ? '.png' : '.jpg');

        return new File([blob], name, { type });
    } catch {
        return file;
    }
}
