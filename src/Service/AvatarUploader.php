<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AvatarUploader
{
    // Le répertoire cible où les avatars seront stockés.
    private $targetDirectory;
    
    // Le service de logging pour enregistrer des informations ou des erreurs.
    private $logger;

    // Le constructeur de la classe qui est appelé lors de l'instanciation de la classe.
    public function __construct(string $targetDirectory, LoggerInterface $logger)
    {
        // Initialisation du répertoire cible et du logger avec les valeurs injectées.
        $this->targetDirectory = $targetDirectory;
        $this->logger = $logger;
    }

    // Fonction pour uploader un fichier.
    public function upload(UploadedFile $file): string
    {
        // Création d'un nom de fichier unique pour éviter les conflits.
        // Utilisation de md5 et uniqid pour générer un nom aléatoire.
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            // Déplacement du fichier téléchargé vers le répertoire cible.
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // Enregistrement de l'erreur dans les logs.
            $this->logger->error('Erreur lors du téléchargement du fichier: '.$e->getMessage());

            // En cas d'erreur lors du déplacement du fichier, une exception est lancée.
            throw new \Exception('Une erreur s’est produite lors du téléchargement du fichier.');

        }

        // Retourne le nom du fichier en cas de réussite.
        return $fileName;
    }

    // Fonction pour obtenir le répertoire cible.
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
?>
