<?php

namespace VirtualBalance\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VirtualBalance\Shared\Utils\Logger;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Application\UseCases\Notification\GetNotificationUseCase;
use VirtualBalance\Application\UseCases\Notification\MarkNotificationAsReadUseCase;
use VirtualBalance\Application\UseCases\Notification\DeleteNotificationsUseCase;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;

class NotificationController
{
    public function __construct(
        private GetNotificationUseCase $getNotificationsUseCase,
        private MarkNotificationAsReadUseCase $markNotificationsAsReadUseCase,
        private DeleteNotificationsUseCase $deleteNotificationsUseCase,
        private UserRepositoryInterface $userRepository
    ) {}

    /** 
     * GET /api/users/{document}/notifications
     * Consulta las notificaciones de un usuario
     */
    public function getNotifications(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $document = $args['document'] ?? '';

        try {
            $user = $this->userRepository->findByDocument($document);
            if (!$user) {
                throw new UserNotFoundException($document);
            }
            $notifications = $this->getNotificationsUseCase->execute($user->getId());

            Logger::info('Notificaciones consultadas exitosamente', ['document' => $document]);

            return ResponseFormatter::success(
                data: $notifications,
                message: 'Notificaciones consultadas exitosamente'
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en consulta de notificaciones', ['document' => $document]);
            return ResponseFormatter::notFound('Usuario no encontrado con el documento: ' . $document);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos inválidos en consulta de notificaciones', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al consultar notificaciones', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al consultar notificaciones');
        }
    }

    /** 
     *  POST /api/users/{document}/notifications/mark-read
     * Marca las notificaciones de un usuario como leídas
     */
    public function markNotificationsAsRead(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $document = $args['document'] ?? '';

        try {
            $user = $this->userRepository->findByDocument($document);
            if (!$user) {
                throw new UserNotFoundException($document);
            }
            $notifications = $this->getNotificationsUseCase->execute($user->getId());
            foreach ($notifications as $notification) {
                $this->markNotificationsAsReadUseCase->execute($notification->id);
            }

            Logger::info('Notificaciones marcadas como leídas exitosamente', ['document' => $document]);

            return ResponseFormatter::success(
                message: 'Notificaciones marcadas como leídas exitosamente'
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en marcado de notificaciones', ['document' => $document]);
            return ResponseFormatter::notFound('Usuario no encontrado con el documento: ' . $document);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos inválidos en marcado de notificaciones', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al marcar notificaciones como leídas', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al marcar notificaciones como leídas');
        }
    }

    /** 
     *  POST /api/users/{document}/notifications/delete
     * Elimina las notificaciones de un usuario
     */
    public function deleteNotifications(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $document = $args['document'] ?? '';

        try {
            $user = $this->userRepository->findByDocument($document);
            if (!$user) {
                throw new UserNotFoundException($document);
            }
            $this->deleteNotificationsUseCase->execute($user->getId());

            Logger::info('Notificaciones eliminadas exitosamente', ['document' => $document]);

            return ResponseFormatter::success(
                message: 'Notificaciones eliminadas exitosamente'
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en eliminación de notificaciones', ['document' => $document]);
            return ResponseFormatter::notFound('Usuario no encontrado con el documento: ' . $document);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos inválidos en eliminación de notificaciones', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al eliminar notificaciones', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al eliminar notificaciones');
        }
    }
}
