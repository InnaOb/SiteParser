<?php

namespace App\Service\Validation;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputValidator
{
    private InputInterface $input;
    private ValidatorInterface $validator;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
        $this->validator = Validation::createValidator();
    }

    public function validate(): void
    {
        $constraints = new Assert\Collection([
            'url' => [
                new Assert\NotBlank(),
                new Assert\Url(),
            ],
            'depth' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer']),
            ],
            'timeout' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer']),
            ],
            'limit' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer']),
            ],
        ]);

        $parameters = [
            'url' => $this->input->getArgument('url'),
            'depth' => (int) $this->input->getArgument('depth'),
            'timeout' => (int) $this->input->getArgument('timeout'),
            'limit' => (int) $this->input->getArgument('limit'),
        ];

        $violations = $this->validator->validate($parameters, $constraints);

        if ($violations->count() > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new InvalidArgumentException(implode("\n", $messages));
        }
    }
}
