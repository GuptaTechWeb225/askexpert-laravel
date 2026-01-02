<?php

namespace App\Traits;

use App\Models\NotificationMessage;
use App\Models\Order;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

trait PushNotificationTrait
{
    use CommonTrait;

    /**
     * @param string $key
     * @param string $type
     * @param object|array $order
     * @param object|array $data
     * @return void
     * push notification order related
     */


    /**
     * chatting related push notification
     * @param string $key
     * @param string $type
     * @param object $userData
     * @param object $messageForm
     * @return void
     */
    protected function chattingNotification(string $key, string $type, object $userData, object $messageForm): void
    {
        try {
            $fcm_token = $type == 'delivery_man' ? $userData?->fcm_token : $userData?->cm_firebase_token;
            if ($fcm_token) {
                $lang = $userData?->app_language ?? getDefaultLanguage();
                $value = $this->pushNotificationMessage($key, $type, $lang);
                if ($value) {
                    $value = $this->textVariableDataFormat(
                        value: $value,
                        key: $key,
                        userName: "{$messageForm?->f_name} ",
                        shopName: "{$messageForm?->shop?->name}",
                        deliveryManName: "{$messageForm?->f_name}",
                        time: now()->diffForHumans()
                    );
                    if ($key == 'message_from_admin') {
                        $messageFromType = 'admin';
                    } elseif ($key == 'message_from_customer') {
                        $messageFromType = 'customer';
                    } elseif ($key == 'message_from_seller') {
                        $messageFromType = 'seller';
                    } elseif ($key == 'message_from_delivery_man') {
                        $messageFromType = 'delivery_man';
                    } else {
                        $messageFromType = '';
                    }
                    $data = [
                        'title' => translate('message'),
                        'description' => $value,
                        'order_id' => '',
                        'image' => '',
                        'type' => 'chatting',
                        'message_key' => $key,
                        'notification_key' => $key,
                        'notification_from' => $messageFromType,
                    ];
                    $this->sendChattingPushNotificationToDevice($fcm_token, $data);
                }
            }
        } catch (\Exception $exception) {
        }
    }



    protected function customerStatusUpdateNotification(string $key, string $type, string $lang, string $status, string $fcmToken): void
    {
        $value = $this->pushNotificationMessage($key, $type, $lang);
        if ($value) {
            $data = [
                'title' => translate('your_account_has_been' . '_' . $status),
                'description' => $value,
                'image' => '',
                'type' => 'block',
                'message_key' => $key,
            ];
            $this->sendPushNotificationToDevice($fcmToken, $data);
        }
    }



    /**
     * push notification variable message format
     */


    /**
     * push notification variable message
     * @param string $key
     * @param string $userType
     * @param string $lang
     * @return false|int|mixed|void
     */



    protected function demoResetNotification(): void
    {
        try {
            $data = [
                'title' => translate('demo_reset_alert'),
                'description' => translate('demo_data_is_being_reset_to_default') . '.',
                'image' => '',
                'order_id' => '',
                'type' => 'demo_reset',
            ];
            $this->sendPushNotificationToTopic(data: $data, topic: $data['type']);
        } catch (\Throwable $th) {
            info('Failed_to_sent_demo_reset_notification');
        }
    }


    /**
     * Device wise notification send
     * @param string $fcmToken
     * @param array $data
     * @return bool|string
     */

    protected function sendPushNotificationToDevice(string $fcmToken, array $data): bool|string
    {
        $postData = [
            'message' => [
                'token' => $fcmToken,
                'data' => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                    'image' => $data['image'],
                    'order_id' => (string)($data['order_id'] ?? ''),
                    'order_details_id' => (string)($data['order_details_id'] ?? ''),
                    'refund_id' => (string)($data['refund_id'] ?? ''),
                    'deliveryman_charge' => (string)($data['deliveryman_charge'] ?? ''),
                    'expected_delivery_date' => (string)($data['expected_delivery_date'] ?? ''),
                    'type' => (string)$data['type'],
                    'is_read' => '0',
                    'message_key' => (string)($data['message_key'] ?? ''),
                    'notification_key' => (string)($data['notification_key'] ?? ''),
                    'notification_from' => (string)($data['notification_from'] ?? ''),
                ],
                'notification' => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                ]
            ]
        ];
        return $this->sendNotificationToHttp($postData);
    }

    /**
     * Device wise notification send
     * @param string $fcmToken
     * @param array $data
     * @return bool|string
     */

    protected function sendChattingPushNotificationToDevice(string $fcmToken, array $data): bool|string
    {
        $postData = [
            'message' => [
                'token' => $fcmToken,
                'data' => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                    'image' => $data['image'],
                    'order_id' => (string)($data['order_id'] ?? ''),
                    'refund_id' => (string)($data['refund_id'] ?? ''),
                    'deliveryman_charge' => (string)($data['deliveryman_charge'] ?? ''),
                    'expected_delivery_date' => (string)($data['expected_delivery_date'] ?? ''),
                    'is_read' => '0',
                    'type' => (string)$data['type'],
                    'message_key' => (string)($data['message_key'] ?? ''),
                    'notification_key' => (string)($data['notification_key'] ?? ''),
                    'notification_from' => (string)($data['notification_from'] ?? ''),
                ],
                'notification' => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                    //                    'type' => (string)$data['type'],
                    //                    'message_key' => (string)($data['message_key'] ?? ''),
                ]
            ]
        ];
        return $this->sendNotificationToHttp($postData);
    }


    /**
     * Device wise notification send
     * @param array|object $data
     * @param string $topic
     * @return bool|string
     */
    protected function sendPushNotificationToTopic(array|object $data, string $topic = 'buiobites'): bool|string
    {
        $postData = [
            'message' => [
                'topic' => $topic,
                'data' => [
                    'title' => (string)($data['title'] ?? ''),
                    'body' => (string)($data['description'] ?? ''),
                    'image' => $data['image'] ?? '',
                    'order_id' => (string)($data['order_id'] ?? ''),
                    'type' => (string)($data['type'] ?? ''),
                    'is_read' => '0'
                ],
                'notification' => [
                    'title' => (string)($data['title'] ?? ''),
                    'body' => (string)($data['description'] ?? ''),
                ]
            ]
        ];

        // 🔹 Log me save karenge
        Log::info('🔔 Sending Push Notification', [
            'topic' => $topic,
            'payload' => $postData
        ]);

        return $this->sendNotificationToHttp($postData);
    }

    protected function sendNotificationToHttp(array|null $data): bool|string|null
    {
        try {
            $key = (array) getWebConfig('push_notification_key');

            if (isset($key['project_id'])) {
                $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';
                $headers = [
                    'Authorization' => 'Bearer ' . $this->getAccessToken($key),
                    'Content-Type'  => 'application/json',
                ];              

                $response = Http::withHeaders($headers)->post($url, $data);
            
                return $response->body();
            }
            return false;
        } catch (\Exception $exception) {
            Log::error("❌ Exception in sendNotificationToHttp", [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);
            return false;
        }
    }


    public function sendPushNotificationV1($token, $title, $body, $imageUrl = null)
    {
        try {
            $key = (array) getWebConfig('push_notification_key');
            if (!isset($key['project_id'], $key['client_email'], $key['private_key'])) {
                return ['error' => 'Push notification config missing'];
            }

            $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';

            $jwtPayload = [
                "iss"   => $key['client_email'],
                "scope" => "https://www.googleapis.com/auth/firebase.messaging",
                "aud"   => "https://oauth2.googleapis.com/token",
                "iat"   => time(),
                "exp"   => time() + 3600,
            ];

            $jwt = JWT::encode($jwtPayload, $key['private_key'], 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $accessToken = $response->json()['access_token'] ?? null;
            if (!$accessToken) return ['error' => 'Access token not found'];

            $data = [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                        "image" => $imageUrl, // 🔹 Image added here
                    ],
                ],
            ];


            $result = Http::withToken($accessToken)->post($url, $data);

            if ($result->ok()) {
            } else {
                Log::error("❌ FCM Notification Failed", [
                    'status' => $result->status(),
                    'body'   => $result->body(),
                ]);
            }

            return $result->json();
        } catch (\Exception $e) {
            Log::error("💥 Exception in sendPushNotificationV1", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }




    public function sendPushNotification($token, $title, $body)
    {
        try {
            $key = (array) getWebConfig('push_notification_key');

            if (!isset($key['project_id'], $key['client_email'], $key['private_key'])) {
                return ['error' => 'Push notification config missing'];
            }

            $url = 'https://fcm.googleapis.com/v1/projects/' . $key['project_id'] . '/messages:send';
            $jwtPayload = [
                "iss"   => $key['client_email'],
                "scope" => "https://www.googleapis.com/auth/firebase.messaging",
                "aud"   => "https://oauth2.googleapis.com/token",
                "iat"   => time(),
                "exp"   => time() + 3600,
            ];

            $jwt = JWT::encode($jwtPayload, $key['private_key'], 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $accessToken = $response->json()['access_token'] ?? null;
            if (!$accessToken) {
                return ['error' => 'Access token not found'];
            }
            $data = [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],
                    "android" => [
                        "priority" => "high",
                    ],
                ],
            ];

            $result = Http::withToken($accessToken)->post($url, $data);
            if ($result->ok()) {
            } else {
             
            }

            return $result->json();
        } catch (\Exception $e) {
            Log::error("💥 Exception in sendPushNotification", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return ['error' => $e->getMessage()];
        }
    }





    protected function getAccessToken($key): string|null
    {
        $jwtToken = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600,
            'iat' => time(),
        ];
        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode($jwtToken));
        $unsignedJwt = $jwtHeader . '.' . $jwtPayload;
        openssl_sign($unsignedJwt, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = $unsignedJwt . '.' . base64_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        return $response->json('access_token') ?? null;
    }
}
