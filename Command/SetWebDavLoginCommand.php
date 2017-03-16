<?php

namespace Inwendo\WebDavClientBundle\Command;

use Inwendo\Auth\LoginBundle\Entity\ServiceAccount;
use Inwendo\WebDav\Common\Model\WebDavLogin;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


class SetWebDavLoginCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('inwendo:webdav:login:create')
            ->setDescription('Set a WebDavLogin')
            ->setDefinition(array(
                new InputArgument('localuser', InputArgument::REQUIRED, 'The local userid or username'),
                new InputArgument('serverurl', InputArgument::REQUIRED, 'The WebDav Server URL'),
                new InputArgument('username', InputArgument::REQUIRED, 'The username to use for the service'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password to use for the service')
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $serverurl = $input->getArgument('serverurl');
        $localuser = $input->getArgument('localuser');


        $userrepository = $this->getContainer()->getParameter("inwendo_auth_login.userrepository");

        // find User by id or username
        $user = $this->getContainer()->get("doctrine")->getRepository($userrepository)->findOneBy(array("id" => $localuser));
        if($user == null){
            $user = $this->getContainer()->get("doctrine")->getRepository($userrepository)->findOneBy(array("username" => $localuser));
            if($user == null){
                throw new \Exception('No user with the id or username found');
            }
        }

        $serviceAccountRepo = $this->getContainer()->get("doctrine")->getRepository("InwendoWebDavClientBundle:WebDavServiceAccount");

        /** @var ServiceAccount $existingAccount */
        $existingAccount = $serviceAccountRepo->findOneBy(array("localUserId" => $user->getId()));
        if($existingAccount != null){
            $webDavLogin = new WebDavLogin();
            $webDavLogin->setWebdavUrl($serverurl);
            $webDavLogin->setWebdavUsername($username);
            $webDavLogin->setWebdavPassword($password);

            $this->getContainer()->get("inwendo.webdavclient.service")->saveWebDavLogin($user->getId(), $webDavLogin);
        }else{
            throw new \Exception('No WebDavServiceAccount with the id or username found. Use inwendo:auth:login:service:account:create to create one!');
        }

        $output->writeln(sprintf('Set WebDavLogin on Remote Service for User with id: <comment>%s</comment>', $user->getId()));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument('localuser')) {
            $question = new Question('Please choose a local user to add:');
            $question->setValidator(function ($localuser) {
                if (empty($localuser)) {
                    throw new \Exception('Localuser can not be empty');
                }

                return $localuser;
            });
            $questions['localuser'] = $question;
        }

        if (!$input->getArgument('serverurl')) {
            $question = new Question('Please enter the serverurl of the service:');
            $question->setValidator(function ($serverurl) {
                if (empty($serverurl)) {
                    throw new \Exception('Serverurl can not be empty');
                }

                return $serverurl;
            });
            $questions['serverurl'] = $question;
        }

        if (!$input->getArgument('username')) {
            $question = new Question('Please enter the username of the service:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $questions['username'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please enter the password of the service:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
