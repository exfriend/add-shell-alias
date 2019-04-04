<?php


namespace Exfriend\AddShellAlias;


class AddAlias
{
    public function all( $name, $command )
    {
        try
        {
            $this->bash( $name, $command );
        }
        catch ( \Exception $e )
        {

        }
        try
        {
            $this->zsh( $name, $command );
        }
        catch ( \Exception $e )
        {

        }
        try
        {
            $this->fish( $name, $command );
        }
        catch ( \Exception $e )
        {

        }
    }

    public function default_shell( $name, $command )
    {
        $shell = $this->user()[ 'shell' ];

        try
        {
            $shell = explode( '/', $shell );
        }
        catch ( \Exception $e )
        {
            throw new \Exception( 'Unknown Shell' );
        }

        $shell = array_pop( $shell );


        if ( $shell == 'bash' )
        {
            return $this->bash( $name, $command );
        }

        if ( $shell == 'fish' )
        {
            return $this->fish( $name, $command );
        }

        throw new \Exception( 'We don\'t support ' . $shell . ' at the moment. Feel free to open a PR.' );

    }

    public function zsh( $name, $command )
    {
        return $this->bash( $name, $command, '.zshrc' );
    }

    public function bash( $name, $command, $filename = '.bashrc' )
    {
        $bashrc_path = $this->user()[ 'home' ] . '/' . $filename;

        if ( !file_exists( $bashrc_path ) )
        {
            throw new \Exception( 'bashrc not found' );
            return false;
        }

        $bashrc = file_get_contents( $bashrc_path );
        if ( strpos( $bashrc, 'alias ' . $name . '=' ) )
        {
            throw new \Exception( 'Alias exists' );
            return false;
        }

        $bashrc .= PHP_EOL . 'alias ' . $name . "='" . $command . "'";

        try
        {
            file_put_contents( $bashrc_path, $bashrc );
        }
        catch ( \Exception $e )
        {
            throw new \Exception( 'File is not writable' );
        }

    }

    public function fish( $name, $command )
    {
        $fish_path = $this->user()[ 'home' ] . '/.config/fish/functions';

        if ( !file_exists( $fish_path ) )
        {
            throw new \Exception( 'fish functions dir not found' );
            return false;
        }

        $b = "function $name; $command; end";

        try
        {
            file_put_contents( $fish_path . '/' . $name . '.fish', $b );
        }
        catch ( \Exception $e )
        {
            throw new \Exception( 'File is not writable' );
        }
    }

    protected function user()
    {
        $user = posix_getpwuid( posix_getuid() );
        return [
            'home'  => $user[ 'dir' ],
            'shell' => $user[ 'shell' ],
        ];
    }
}