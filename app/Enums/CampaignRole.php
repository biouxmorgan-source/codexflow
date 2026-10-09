<?php

namespace App\Enums;

/**
 * Rôle d'un compte dans une campagne. Le propriétaire est le MJ qui l'a créée (campaigns.user_id) ;
 * un autre MJ de la campagne en est co-MJ.
 */
enum CampaignRole: string
{
    case GameMaster = 'gm';
    case Player = 'player';
    case Spectator = 'spectator';

    public function label(): string
    {
        return match ($this) {
            self::GameMaster => __('Maître de jeu'),
            self::Player => __('Joueur'),
            self::Spectator => __('Spectateur'),
        };
    }

    /** Libellé pour un membre invité : un MJ invité est co-MJ, le propriétaire restant le MJ. */
    public function invitedLabel(): string
    {
        return $this === self::GameMaster ? __('Co-MJ') : $this->label();
    }

    /** Ce que le rôle permet, en une phrase, pour le choisir en invitant. */
    public function description(): string
    {
        return match ($this) {
            self::GameMaster => __('Prépare et mène avec vous, avec accès à la zone MJ. Ne gère ni les membres ni la suppression.'),
            self::Player => __('Joue un personnage et voit ce que ce personnage connaît.'),
            self::Spectator => __("Regarde l'écran de table, même quand il n'est pas partagé aux joueurs (une télé, un projecteur), sans rien d'autre."),
        };
    }
}
