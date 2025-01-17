<?php

/**
 * New Tm User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Data\Mapper\Lva;

use Common\Service\Helper\FlashMessengerHelperService;
use Mockery as m;
use Common\Data\Mapper\Lva\NewTmUser;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Form;

/**
 * New Tm User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NewTmUserTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $this->assertEquals(['foo' => 'bar'], NewTmUser::mapFromResult(['foo' => 'bar']));
    }

    public function testMapFormErrors()
    {
        $form = m::mock(Form::class);
        $fm = m::mock(FlashMessengerHelperService::class);

        $messages = [
            'username' => [
                'username-error'
            ],
            'emailAddress' => [
                'emailAddress-error'
            ],
            'other-error'
        ];

        $form->shouldReceive('setMessages')->once()
            ->with(
                [
                    'data' => [
                        'username' => [
                            'username-error'
                        ],
                        'emailAddress' => [
                            'emailAddress-error'
                        ]
                    ]
                ]
            );

        $fm->shouldReceive('addCurrentErrorMessage')->once()->with('other-error');

        NewTmUser::mapFormErrors($form, $messages, $fm);
    }

    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm)
    {
        $formMessages = [];

        if (isset($errors['username'])) {

            foreach ($errors['username'] as $key => $message) {
                $formMessages['data']['username'][] = $message;
            }

            unset($errors['username']);
        }

        if (isset($errors['emailAddress'])) {

            foreach ($errors['emailAddress'] as $key => $message) {
                $formMessages['data']['emailAddress'][] = $message;
            }

            unset($errors['emailAddress']);
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
