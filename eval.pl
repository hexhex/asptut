#!/usr/bin/perl
use strict;
use warnings;
use Switch;
use CGI;
use Sys::Hostname;
require Encode;

my $wait_for_keypress = 1;
sub wait_for_keypress()
{
    my ($msg) = @_;

    return unless $wait_for_keypress;
    print "$msg\n" if $msg;
    print "Press 'Return' to continue. (Enter \"nowait\" to run free)\n";
    my $input = <STDIN>;
    $wait_for_keypress = 0 if $input =~ /nowait/;
}


sub trim
{
    my $string = shift;
    for ($string)
    {
        s/^\s+//;
        s/\s+$//;
    }
    return $string;
}

my $host = lc(hostname());

my $plugindir = '';
my $error = '';
my $resultlimit = 60000;

if ($host eq 'roman')
{
    #$ENV{'MALLOC_CHECK_'} = '0';
    $ENV{'GLIBCXX_FORCE_NEW'} = '1';
    $ENV{'LD_LIBRARY_PATH'} = '/home/roman/localinstall/ACE_wrappers/lib';
    $ENV{'PATH'} = '/home/roman/localinstall/bin';
    $plugindir = '/home/roman/.dlvhex/plugins';
}
elsif ($host eq 'apolleres')
{
    #$ENV{'MALLOC_CHECK_'} = '0';
    $ENV{'GLIBCXX_FORCE_NEW'} = '1';
    $ENV{'LD_LIBRARY_PATH'} = '/usr/local/lib/dlvhex';
    $ENV{'PATH'} = '/usr/local/bin';
    $plugindir = '/usr/local/lib/dlvhex/plugins';
}

my $cgi = new CGI;

my $fs = '';

my $lp = $cgi->param('lp');
$fs = trim($cgi->param('filter'));
my $solver = $cgi->param('solver');
my $ontology = trim($cgi->param('dlpont'));
my $ofpl = $cgi->param('ofpl');

my $solverexec = '';

# escape quotes in filter string
my $filter = $fs;
$filter =~ s/\"/\\\"/g;

my @result = ();

switch ($solver)
{
case 'dlv'
    { $solverexec = 'dlv -silent -filter=' . $filter; }
case 'dlt'
    { $solverexec = 'dlt -silent -filter=' . $filter; }
case 'dlvhex'
    { $solverexec = 'dlvhex --silent --plugindir=' . $plugindir . ' --filter=' . $filter; }
case 'dlvhex+dlt'
    { $solverexec = 'dlvhex --silent --dlt --plugindir=' . $plugindir . ' --filter=' . $filter; }
case 'dlvhex+dlt'
    { $solverexec = 'dlvhex --silent --dlt --plugindir=' . $plugindir . ' --filter=' . $filter; }
case 'dlp'
{
    if ($ontology eq '') { $error = 'error: no ontology specified!'; }
    else
    { $solverexec = 'dlvhex --silent --dlt --plugindir=' . $plugindir . ' --ontology=' . $ontology . ' --filter=' . $filter; }
}
else
    { $error = 'error: no solver specified!'; }
}

my $filename = '';

if ($error eq '')
{
    my $salt=join '', (0..9, 'A'..'Z', 'a'..'z')[rand 64, rand 64];
    $filename = "tempfiles/lp$$".time.$salt.".tmp";

    open(FH, "> $filename");
    print FH $lp;
    close(FH);

    my $finished = 1;
    my $totalsize = 0;
    # print "Opening $solverexec $filename\n";
    # &wait_for_keypress();
    my $pid = open(SOLVER, "$solverexec $filename 2>&1 |");
    while (<SOLVER>)
    {
        push(@result, $_);
        $totalsize = $totalsize + length($_);

        if ($totalsize > $resultlimit)
        {
            push(@result, '<br/>Output too long, cut here!'); 
            $finished = 0;
            last;
        }
    }
    if ($finished == 1)
        { close(SOLVER); }
    else
        { kill 9, $pid; }

    #@result = `echo '$lp' | $solverexec -- 2>&1`;

    unlink $filename;
}


#
# output starts
#

print $cgi->header(-'Cache-Control'=>'no-cache, must-revalidate, max-age=0',
                   -expires=>'Mon, 26 Jul 1997 05:00:00 GMT',
                   -charset=>'utf-8');

print '<h3>Result:</h3>';

if ($error ne '') { print $error; exit 0; }

if ($fs ne '') { print '<p>filter: ' . $fs . '</p>'; }

my $as = 0;

foreach my $line (@result)
{
    $line =~ s/$filename: //g;
    $line =~ s/Best model: //g;
    $line =~ s/(http:\/\/[^"]*)/<a href=\"$1\">$1<\/a>/g;
    if (substr($line,0,1) eq '{') { $as++; print '<b>Answer Set '.$as.'</b>:<br/>'; }
    if ($ofpl eq 'true') { $line =~ s/, /,<br\/>/g; }
    print '<p>'.$line.'</p>';
}

